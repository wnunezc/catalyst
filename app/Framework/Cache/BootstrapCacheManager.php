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

namespace Catalyst\Framework\Cache;

/**
 * Defines the Bootstrap Cache Manager class contract.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Coordinates the bootstrap cache manager behavior within its module boundary.
 */
final class BootstrapCacheManager
{
    /**
     * Loads the requested data.
     */
    public static function loadConfigCache(): ?array
    {
        if (!CacheSettings::featureEnabled('config_cache')) {
            return null;
        }

        return self::loadPhpArrayFile(self::configCacheFile());
    }

    /**
     * Handles the sync config cache workflow.
     */
    public static function syncConfigCache(array $config): void
    {
        $settings = CacheSettings::current();

        if ((bool) ($settings['cache_enabled'] ?? false) && (bool) ($settings['config_cache'] ?? false)) {
            self::buildConfigCache($config);
            return;
        }

        self::clearConfigCache();
    }

    /**
     * Builds the requested structure.
     */
    public static function buildConfigCache(array $config): bool
    {
        return self::writePhpArrayFile(self::configCacheFile(), $config);
    }

    /**
     * Handles the clear config cache workflow.
     */
    public static function clearConfigCache(): bool
    {
        return self::clearFile(self::configCacheFile());
    }

    /**
     * @return string[]|null
     */
    public static function loadDiscoveryCache(): ?array
    {
        if (!CacheSettings::featureEnabled('discovery_cache')) {
            return null;
        }

        $files = self::loadPhpArrayFile(self::discoveryCacheFile());
        if ($files === null) {
            return null;
        }

        $resolved = [];

        foreach ($files as $file) {
            if (is_string($file) && is_file($file)) {
                $resolved[] = $file;
            }
        }

        return $resolved;
    }

    /**
     * @param string[] $files
     */
    public static function syncDiscoveryCache(array $files): void
    {
        $settings = CacheSettings::current();

        if ((bool) ($settings['cache_enabled'] ?? false) && (bool) ($settings['discovery_cache'] ?? false)) {
            self::buildDiscoveryCache($files);
            return;
        }

        self::clearDiscoveryCache();
    }

    /**
     * @param string[] $files
     */
    public static function buildDiscoveryCache(array $files): bool
    {
        return self::writePhpArrayFile(self::discoveryCacheFile(), array_values($files));
    }

    /**
     * Handles the clear discovery cache workflow.
     */
    public static function clearDiscoveryCache(): bool
    {
        return self::clearFile(self::discoveryCacheFile());
    }

    /**
     * Handles the clear all workflow.
     */
    public static function clearAll(): bool
    {
        return self::clearConfigCache() && self::clearDiscoveryCache();
    }

    /**
     * Handles the config cache file workflow.
     */
    public static function configCacheFile(): string
    {
        return self::bootstrapDirectory() . DS . 'config.' . CacheSettings::environment() . '.php';
    }

    /**
     * Handles the discovery cache file workflow.
     */
    public static function discoveryCacheFile(): string
    {
        return self::bootstrapDirectory() . DS . 'discovery.' . CacheSettings::environment() . '.php';
    }

    /**
     * Bootstraps the runtime workflow.
     */
    private static function bootstrapDirectory(): string
    {
        return implode(DS, [PD, 'cache', 'bootstrap']);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function loadPhpArrayFile(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }

        $payload = require $file;

        return is_array($payload) ? $payload : null;
    }

    /**
     * @param array<string, mixed>|string[] $payload
     */
    private static function writePhpArrayFile(string $file, array $payload): bool
    {
        $directory = dirname($file);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return false;
        }

        $encoded = '<?php return ' . var_export($payload, true) . ';';
        $temp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';

        if (file_put_contents($temp, $encoded, LOCK_EX) === false) {
            @unlink($temp);
            return false;
        }

        if (!@rename($temp, $file)) {
            @unlink($temp);
            return false;
        }

        return true;
    }

    /**
     * Handles the clear file workflow.
     */
    private static function clearFile(string $file): bool
    {
        return !is_file($file) || @unlink($file);
    }
}

