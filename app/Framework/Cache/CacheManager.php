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

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Defines the Cache Manager class contract.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Coordinates the cache manager behavior within its module boundary.
 */
final class CacheManager
{
    use SingletonTrait;

    private CacheStoreInterface $store;

    private string $fingerprint = '';

    /**
     * Initializes the Cache Manager instance.
     */
    protected function __construct()
    {
        $this->store = new NullCacheStore();
    }

    /**
     * Handles the persistence workflow.
     */
    public function store(): CacheStoreInterface
    {
        $config = CacheSettings::current();
        $fingerprint = sha1(json_encode([
            'env' => CacheSettings::environment(),
            'cache_enabled' => (bool) ($config['cache_enabled'] ?? false),
            'app_cache' => (bool) ($config['app_cache'] ?? false),
            'driver' => (string) ($config['cache_driver'] ?? 'file'),
            'prefix' => (string) ($config['cache_prefix'] ?? 'catalyst_'),
        ], JSON_THROW_ON_ERROR));

        if ($this->fingerprint !== $fingerprint) {
            $this->fingerprint = $fingerprint;
            $this->store = $this->resolveStore($config);
        }

        return $this->store;
    }

    /**
     * Returns the runtime value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store()->get($key, $default);
    }

    /**
     * Handles the put workflow.
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        return $this->store()->put($key, $value, $ttlSeconds);
    }

    /**
     * Handles the forever workflow.
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->store()->forever($key, $value);
    }

    /**
     * Handles the has workflow.
     */
    public function has(string $key): bool
    {
        return $this->store()->has($key);
    }

    /**
     * Handles the forget workflow.
     */
    public function forget(string $key): bool
    {
        return $this->store()->forget($key);
    }

    /**
     * Handles the clear workflow.
     */
    public function clear(): bool
    {
        return $this->store()->clear();
    }

    /**
     * Handles the remember workflow.
     */
    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        return $this->store()->remember($key, $resolver, $ttlSeconds);
    }

    /**
     * Handles the refresh workflow.
     */
    public function refresh(): void
    {
        $this->fingerprint = '';
        $this->store = new NullCacheStore();
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $config = CacheSettings::current();
        $store = $this->store();

        return [
            'environment' => CacheSettings::environment(),
            'runtime_enabled' => CacheSettings::runtimeEnabled($config),
            'cache_enabled' => (bool) ($config['cache_enabled'] ?? false),
            'driver' => $store->getDriverName(),
            'prefix' => (string) ($config['cache_prefix'] ?? 'catalyst_'),
            'app_cache' => (bool) ($config['app_cache'] ?? false),
            'config_cache' => (bool) ($config['config_cache'] ?? false),
            'discovery_cache' => (bool) ($config['discovery_cache'] ?? false),
            'route_cache' => (bool) ($config['route_cache'] ?? false),
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    private function resolveStore(array $config): CacheStoreInterface
    {
        if (!CacheSettings::featureEnabled('app_cache', $config)) {
            return new NullCacheStore();
        }

        return match (strtolower((string) ($config['cache_driver'] ?? 'file'))) {
            'array' => new ArrayCacheStore(),
            'null' => new NullCacheStore(),
            default => new FileCacheStore(implode(DS, [PD, 'cache', 'data']), (string) ($config['cache_prefix'] ?? 'catalyst_')),
        };
    }
}

