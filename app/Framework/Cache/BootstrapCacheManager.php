<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cache;

final class BootstrapCacheManager
{
    public static function loadConfigCache(): ?array
    {
        if (!CacheSettings::featureEnabled('config_cache')) {
            return null;
        }

        return self::loadPhpArrayFile(self::configCacheFile());
    }

    public static function syncConfigCache(array $config): void
    {
        $settings = CacheSettings::current();

        if ((bool) ($settings['cache_enabled'] ?? false) && (bool) ($settings['config_cache'] ?? false)) {
            self::buildConfigCache($config);
            return;
        }

        self::clearConfigCache();
    }

    public static function buildConfigCache(array $config): bool
    {
        return self::writePhpArrayFile(self::configCacheFile(), $config);
    }

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

    public static function clearDiscoveryCache(): bool
    {
        return self::clearFile(self::discoveryCacheFile());
    }

    public static function clearAll(): bool
    {
        return self::clearConfigCache() && self::clearDiscoveryCache();
    }

    public static function configCacheFile(): string
    {
        return self::bootstrapDirectory() . DS . 'config.' . CacheSettings::environment() . '.php';
    }

    public static function discoveryCacheFile(): string
    {
        return self::bootstrapDirectory() . DS . 'discovery.' . CacheSettings::environment() . '.php';
    }

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

    private static function clearFile(string $file): bool
    {
        return !is_file($file) || @unlink($file);
    }
}

