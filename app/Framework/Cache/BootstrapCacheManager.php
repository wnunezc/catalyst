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
 * Manages generated configuration and discovery cache files.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Loads, writes and clears bootstrap cache artifacts atomically.
 */
final class BootstrapCacheManager
{
    /**
     * Loads the generated configuration cache when enabled.
     */
    public static function loadConfigCache(): ?array
    {
        if (!CacheSettings::featureEnabled('config_cache')) {
            return null;
        }

        return self::loadPhpArrayFile(self::configCacheFile());
    }

    /**
     * Builds or removes the configuration cache according to settings.
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
     * Writes the generated configuration cache file.
     */
    public static function buildConfigCache(array $config): bool
    {
        return self::writePhpArrayFile(self::configCacheFile(), $config);
    }

    /**
     * Removes the generated configuration cache file.
     */
    public static function clearConfigCache(): bool
    {
        return self::clearFile(self::configCacheFile());
    }

    /**
     * Loads existing discovery paths from the generated cache.
     *
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
     * Builds or removes the discovery cache according to settings.
     *
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
     * Writes the generated discovery cache file.
     *
     * @param string[] $files
     */
    public static function buildDiscoveryCache(array $files): bool
    {
        return self::writePhpArrayFile(self::discoveryCacheFile(), array_values($files));
    }

    /**
     * Removes the generated discovery cache file.
     */
    public static function clearDiscoveryCache(): bool
    {
        return self::clearFile(self::discoveryCacheFile());
    }

    /**
     * Removes all generated bootstrap cache files.
     */
    public static function clearAll(): bool
    {
        return self::clearConfigCache() && self::clearDiscoveryCache();
    }

    /**
     * Returns the configuration cache artifact path.
     */
    public static function configCacheFile(): string
    {
        return self::bootstrapDirectory() . DS . 'config.' . CacheSettings::environment() . '.php';
    }

    /**
     * Returns the discovery cache artifact path.
     */
    public static function discoveryCacheFile(): string
    {
        return self::bootstrapDirectory() . DS . 'discovery.' . CacheSettings::environment() . '.php';
    }

    /**
     * Returns the bootstrap cache directory.
     */
    private static function bootstrapDirectory(): string
    {
        return implode(DS, [PD, 'cache', 'bootstrap']);
    }

    /**
     * Loads an array from a generated PHP cache file.
     *
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
     * Atomically writes an array to a generated PHP cache file.
     *
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
     * Removes a generated cache file when present.
     */
    private static function clearFile(string $file): bool
    {
        return !is_file($file) || @unlink($file);
    }
}
