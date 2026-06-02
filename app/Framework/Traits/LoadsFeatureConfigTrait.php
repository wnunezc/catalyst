<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Traits;

use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Log\Logger;

/**
 * LoadsFeatureConfigTrait — JSON config loader with graceful degradation.
 *
 * Loads a named section from boot-core/config/{env}/{section}.json via
 * ConfigManager and merges it with caller-supplied defaults.
 *
 * Degradation rules:
 *   - Section missing in JSON  → defaults applied, warning logged, E_USER_NOTICE in dev.
 *   - ConfigManager throws     → defaults applied, error logged — request never broken.
 *   - 'enabled' key absent     → defaults to true (feature active).
 *
 * Usage:
 *   use LoadsFeatureConfigTrait;
 *
 *   protected function process(...): Response
 *   {
 *       $cfg = $this->loadFeatureSection('cors', self::DEFAULTS);
 *       if (!$cfg['enabled']) { return $this->passToNext($request, $next); }
 *       // ... feature logic using $cfg['allowed_origins'] etc.
 *   }
 *
 * @package Catalyst\Framework\Traits
 */
trait LoadsFeatureConfigTrait
{
    /** @var array<string, mixed> Cached resolved config after first load. */
    private array $featureData = [];

    private bool $featureReady = false;

    /**
     * Load a config section, merging JSON values over $defaults.
     * Cached per instance: subsequent calls return the same array.
     *
     * @param string               $section  Key matching the JSON file and inner key (e.g. 'cors')
     * @param array<string, mixed> $defaults Fallback values; 'enabled'=>true is always injected
     * @return array<string, mixed>
     */
    protected function loadFeatureSection(string $section, array $defaults = []): array
    {
        if ($this->featureReady) {
            return $this->featureData;
        }

        $this->featureData  = array_merge(['enabled' => true], $defaults);
        $this->featureReady = true;

        try {
            $raw  = ConfigManager::getInstance()->section($section);
            $data = $raw[$section] ?? null;

            if ($data === null) {
                $this->warnMissingConfig($section);
                return $this->featureData;
            }

            $this->featureData = array_merge($this->featureData, $data);

        } catch (\Throwable $e) {
            Logger::getInstance()->error(
                "LoadsFeatureConfigTrait: failed loading section '{$section}'",
                ['error' => $e->getMessage()]
            );
        }

        return $this->featureData;
    }

    /**
     * Handles the warn missing config workflow.
     */
    private function warnMissingConfig(string $section): void
    {
        $msg = "Feature config '{$section}' not found — defaults applied";
        Logger::getInstance()->warning($msg);

        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            trigger_error($msg, E_USER_NOTICE);
        }
    }
}
