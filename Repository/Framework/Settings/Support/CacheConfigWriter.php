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

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Framework\Cache\BootstrapCacheManager;
use Catalyst\Framework\Cache\CacheManager;
use Catalyst\Framework\Cache\CacheSettings;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Route\Router;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Defines the Cache Config Writer class contract.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Coordinates the cache config writer behavior within its module boundary.
 */
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
