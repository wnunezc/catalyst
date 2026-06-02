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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Config\ConfigSecretCatalog;

/**
 * config:secrets:sync CLI command.
 *
 * Responsibility: Runs the config:secrets:sync command to Move managed secret keys out of public JSON config into the companion secret store.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class ConfigSecretsSyncCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'config:secrets:sync';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Move managed secret keys out of public JSON config into the companion secret store';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $config = ConfigManager::getInstance();
        $touchedSections = [];

        foreach (ConfigSecretCatalog::managedSections() as $section) {
            $effectiveSection = $config->section($section);

            if (!is_array($effectiveSection)) {
                continue;
            }

            $split = ConfigSecretCatalog::splitSection($section, $effectiveSection);
            $config->writeSection($section, $effectiveSection);
            $touchedSections[$section] = $this->countSecrets($split['secrets']);
        }

        $leaks = $config->secretStore()->publicSecretLeaks();

        if ($leaks !== []) {
            $this->error('Secret sync completed with leaks still present in public JSON: ' . implode(', ', $leaks));
            return 1;
        }

        $this->success('Config secrets synced successfully.');
        $this->line('  Secret store: ' . $config->secretStore()->path());

        if ($touchedSections === []) {
            $this->warn('  No managed config sections were present in the active environment.');
            return 0;
        }

        foreach ($touchedSections as $section => $secretCount) {
            $this->line(sprintf('  %s: %d secret value(s)', $section, $secretCount));
        }

        return 0;
    }

    /**
     * Describes the count secrets helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the count secrets helper workflow used by this CLI component.
     */
    private function countSecrets(array $payload): int
    {
        $count = 0;

        foreach ($payload as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            foreach ($entry as $value) {
                if ($value !== null && $value !== '') {
                    $count++;
                }
            }
        }

        return $count;
    }
}
