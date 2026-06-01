<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Framework\Cache\BootstrapCacheManager;
use Catalyst\Framework\Cache\CacheManager;
use Catalyst\Framework\Cache\CacheSettings;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Route\Router;
use Catalyst\Helpers\Config\ConfigManager;

final class CacheConfigWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        $config = ConfigManager::getInstance();

        CacheManager::getInstance()->clear();

        $payload = [
            'cache' => [
                'cache_enabled' => (bool) ($data['cache_enabled'] ?? false),
                'cache_driver' => (string) ($data['cache_driver'] ?? 'file'),
                'cache_prefix' => (string) (($data['cache_prefix'] ?? '') !== '' ? $data['cache_prefix'] : 'catalyst_'),
                'app_cache' => (bool) ($data['app_cache'] ?? false),
                'config_cache' => (bool) ($data['config_cache'] ?? false),
                'discovery_cache' => (bool) ($data['discovery_cache'] ?? false),
                'route_cache' => (bool) ($data['route_cache'] ?? false),
            ],
        ];

        $config->writeSection('cache', $payload);
        CacheManager::getInstance()->refresh();

        $saved = CacheSettings::current();

        if (!$saved['app_cache']) {
            CacheManager::getInstance()->clear();
        }

        if (!$saved['route_cache']) {
            Router::getInstance()->clearRouteCache();
        }

        if (!$saved['discovery_cache']) {
            BootstrapCacheManager::clearDiscoveryCache();
        } else {
            BootstrapCacheManager::syncDiscoveryCache(CliRouteLoader::routeFiles());
        }
    }
}
