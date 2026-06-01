<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cache;

use Catalyst\Framework\Traits\SingletonTrait;

final class CacheManager
{
    use SingletonTrait;

    private CacheStoreInterface $store;

    private string $fingerprint = '';

    protected function __construct()
    {
        $this->store = new NullCacheStore();
    }

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

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store()->get($key, $default);
    }

    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        return $this->store()->put($key, $value, $ttlSeconds);
    }

    public function forever(string $key, mixed $value): bool
    {
        return $this->store()->forever($key, $value);
    }

    public function has(string $key): bool
    {
        return $this->store()->has($key);
    }

    public function forget(string $key): bool
    {
        return $this->store()->forget($key);
    }

    public function clear(): bool
    {
        return $this->store()->clear();
    }

    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        return $this->store()->remember($key, $resolver, $ttlSeconds);
    }

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

