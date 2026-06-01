<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cache;

final class NullCacheStore implements CacheStoreInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        return false;
    }

    public function forever(string $key, mixed $value): bool
    {
        return false;
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function forget(string $key): bool
    {
        return true;
    }

    public function clear(): bool
    {
        return true;
    }

    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        return $resolver();
    }

    public function getDriverName(): string
    {
        return 'null';
    }
}

