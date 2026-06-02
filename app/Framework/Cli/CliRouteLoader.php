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

namespace Catalyst\Framework\Cli;

use Catalyst\Framework\Cache\BootstrapCacheManager;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Route loader used by CLI commands.
 *
 * Responsibility: Loads global, API, framework and application route files in kernel-compatible order.
 *
 * @package Catalyst\Framework\Cli
 */
final class CliRouteLoader
{
    /**
     * Requires every route file in kernel-compatible order.
     */
    public static function loadAll(): void
    {
        foreach (self::routeFiles() as $file) {
            require_once $file;
        }
    }

    /**
     * Returns route files from fresh discovery or production cache.
     *
     * @return string[]
     */
    public static function routeFiles(): array
    {
        if (ConfigManager::getInstance()->getEnvironment() !== 'production') {
            return self::discoverFreshRouteFiles();
        }

        $cachedManifest = BootstrapCacheManager::loadDiscoveryCache();
        if (is_array($cachedManifest) && $cachedManifest !== []) {
            return self::orderRouteFiles($cachedManifest);
        }

        return self::discoverFreshRouteFiles();
    }

    /**
     * Discovers route files from global boot routes and active modules.
     *
     * @return string[]
     */
    public static function discoverFreshRouteFiles(): array
    {
        $files = [];

        foreach ([
            implode(DS, [PD, 'boot-core', 'routes', 'global-routes.php']),
            implode(DS, [PD, 'boot-core', 'routes', 'api.php']),
        ] as $file) {
            if (is_file($file)) {
                $files[] = $file;
            }
        }

        foreach (ModuleRegistry::getInstance()->active() as $module) {
            $file = $module['route_file'] ?? null;
            if (!is_string($file) || $file === '' || !is_file($file)) {
                continue;
            }

            $files[] = $file;
        }

        $files = self::orderRouteFiles($files);

        BootstrapCacheManager::syncDiscoveryCache($files);

        return $files;
    }

    /**
     * Orders route files by global, API, framework, application and fallback groups.
     *
     * @param string[] $files
     * @return string[]
     */
    private static function orderRouteFiles(array $files): array
    {
        $groups = [
            'global' => [],
            'api' => [],
            'global_other' => [],
            'framework' => [],
            'app' => [],
            'other' => [],
        ];

        foreach (array_values(array_unique($files)) as $file) {
            $normalized = str_replace(['/', '\\'], DS, $file);

            if (str_contains($normalized, implode(DS, ['boot-core', 'routes']))) {
                $group = match (basename($normalized)) {
                    'global-routes.php' => 'global',
                    'api.php' => 'api',
                    default => 'global_other',
                };
                $groups[$group][] = $file;
                continue;
            }

            if (str_contains($normalized, implode(DS, ['Repository', 'Framework']))) {
                $groups['framework'][] = $file;
                continue;
            }

            if (str_contains($normalized, implode(DS, ['Repository', 'App', 'Surface']))) {
                $groups['app'][] = $file;
                continue;
            }

            $groups['other'][] = $file;
        }

        foreach ($groups as &$group) {
            sort($group);
        }
        unset($group);

        return array_merge(
            $groups['global'],
            $groups['api'],
            $groups['global_other'],
            $groups['framework'],
            $groups['app'],
            $groups['other']
        );
    }
}
