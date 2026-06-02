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
 * Defines the Cache Settings class contract.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Coordinates the cache settings behavior within its module boundary.
 */
final class CacheSettings
{
    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'cache_enabled' => false,
            'cache_driver' => 'file',
            'cache_prefix' => 'catalyst_',
            'app_cache' => false,
            'config_cache' => false,
            'discovery_cache' => false,
            'route_cache' => false,
        ];
    }

    /**
     * Handles the environment workflow.
     */
    public static function environment(): string
    {
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            return 'development';
        }

        if (defined('IS_STAGING') && IS_STAGING) {
            return 'staging';
        }

        if (defined('IS_TESTING') && IS_TESTING) {
            return 'testing';
        }

        return 'production';
    }

    /**
     * Handles the config path workflow.
     */
    public static function configPath(?string $environment = null): string
    {
        return implode(DS, [PD, 'boot-core', 'config', $environment ?? self::environment(), 'cache.json']);
    }

    /**
     * @return array<string, mixed>
     */
    public static function current(?string $environment = null): array
    {
        $config = array_replace(self::defaults(), self::readRaw($environment));
        $config['cache_driver'] = self::normalizeDriver((string) ($config['cache_driver'] ?? 'file'));
        $config['cache_prefix'] = self::normalizePrefix((string) ($config['cache_prefix'] ?? 'catalyst_'));

        foreach (['cache_enabled', 'app_cache', 'config_cache', 'discovery_cache', 'route_cache'] as $flag) {
            $config[$flag] = (bool) ($config[$flag] ?? false);
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public static function readRaw(?string $environment = null): array
    {
        $path = self::configPath($environment);
        if (!is_file($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return [];
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return [];
        }

        $section = $decoded['cache'] ?? null;

        return is_array($section) ? $section : [];
    }

    /**
     * @param array<string, mixed>|null $config
     */
    public static function runtimeEnabled(?array $config = null): bool
    {
        $resolved = $config ?? self::current();

        return defined('IS_PRODUCTION')
            && IS_PRODUCTION
            && (bool) ($resolved['cache_enabled'] ?? false);
    }

    /**
     * @param array<string, mixed>|null $config
     */
    public static function featureEnabled(string $feature, ?array $config = null): bool
    {
        $resolved = $config ?? self::current();

        return self::runtimeEnabled($resolved) && (bool) ($resolved[$feature] ?? false);
    }

    /**
     * @param array<string, mixed>|null $config
     */
    public static function configuredFeature(string $feature, ?array $config = null): bool
    {
        $resolved = $config ?? self::current();

        return (bool) ($resolved[$feature] ?? false);
    }

    /**
     * Normalizes the provided value.
     */
    private static function normalizeDriver(string $driver): string
    {
        $driver = strtolower(trim($driver));

        return in_array($driver, ['file', 'array', 'null'], true) ? $driver : 'file';
    }

    /**
     * Normalizes the provided value.
     */
    private static function normalizePrefix(string $prefix): string
    {
        $prefix = trim($prefix);

        return $prefix !== '' ? $prefix : 'catalyst_';
    }
}

