<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli;

use Catalyst\Framework\Cache\BootstrapCacheManager;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Helpers\Config\ConfigManager;

final class CliRouteLoader
{
    public static function loadAll(): void
    {
        foreach (self::routeFiles() as $file) {
            require_once $file;
        }
    }

    /**
     * @return string[]
     */
    public static function routeFiles(): array
    {
        if (ConfigManager::getInstance()->getEnvironment() !== 'production') {
            return self::discoverFreshRouteFiles();
        }

        $cachedManifest = BootstrapCacheManager::loadDiscoveryCache();
        if (is_array($cachedManifest) && $cachedManifest !== []) {
            return $cachedManifest;
        }

        return self::discoverFreshRouteFiles();
    }

    /**
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

        $files = array_values(array_unique($files));
        sort($files);

        BootstrapCacheManager::syncDiscoveryCache($files);

        return $files;
    }
}
