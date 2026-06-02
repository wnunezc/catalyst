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
 * Selects and exposes the configured runtime cache store.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Resolves cache drivers from runtime settings and forwards cache operations.
 */
final class CacheManager
{
    use SingletonTrait;

    private CacheStoreInterface $store;

    private string $fingerprint = '';

    /**
     * Initializes the Cache Manager instance.
     *
     * Responsibility: Initializes the Cache Manager instance.
     */
    protected function __construct()
    {
        $this->store = new NullCacheStore();
    }

    /**
     * Returns the configured cache store, rebuilding it when settings change.
     *
     * Responsibility: Returns the configured cache store, rebuilding it when settings change.
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
     * Returns a cached value or the supplied default.
     *
     * Responsibility: Returns a cached value or the supplied default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store()->get($key, $default);
    }

    /**
     * Stores a value with an optional time-to-live.
     *
     * Responsibility: Stores a value with an optional time-to-live.
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        return $this->store()->put($key, $value, $ttlSeconds);
    }

    /**
     * Stores a value without expiration.
     *
     * Responsibility: Stores a value without expiration.
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->store()->forever($key, $value);
    }

    /**
     * Determines whether a usable cache entry exists.
     *
     * Responsibility: Determines whether a usable cache entry exists.
     */
    public function has(string $key): bool
    {
        return $this->store()->has($key);
    }

    /**
     * Removes one cache entry.
     *
     * Responsibility: Removes one cache entry.
     */
    public function forget(string $key): bool
    {
        return $this->store()->forget($key);
    }

    /**
     * Removes every entry from the active store.
     *
     * Responsibility: Removes every entry from the active store.
     */
    public function clear(): bool
    {
        return $this->store()->clear();
    }

    /**
     * Returns a cached value or stores the resolver result.
     *
     * Responsibility: Returns a cached value or stores the resolver result.
     */
    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        return $this->store()->remember($key, $resolver, $ttlSeconds);
    }

    /**
     * Invalidates the resolved driver so it is rebuilt on next use.
     *
     * Responsibility: Invalidates the resolved driver so it is rebuilt on next use.
     */
    public function refresh(): void
    {
        $this->fingerprint = '';
        $this->store = new NullCacheStore();
    }

    /**
     * Returns a diagnostic summary of cache configuration and driver state.
     *
     * Responsibility: Returns a diagnostic summary of cache configuration and driver state.
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
     * Resolves the configured runtime cache driver.
     *
     * Responsibility: Resolves the configured runtime cache driver.
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
