<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cache;

final class ArrayCacheStore implements CacheStoreInterface
{
    /** @var array<string, array{expires_at:int|null,value:mixed}> */
    private array $items = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->items[$key]['value'] ?? $default;
    }

    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        $this->items[$key] = [
            'expires_at' => $ttlSeconds > 0 ? (time() + $ttlSeconds) : null,
            'value' => $value,
        ];

        return true;
    }

    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value);
    }

    public function has(string $key): bool
    {
        if (!isset($this->items[$key])) {
            return false;
        }

        $expiresAt = $this->items[$key]['expires_at'];
        if ($expiresAt !== null && $expiresAt < time()) {
            unset($this->items[$key]);
            return false;
        }

        return true;
    }

    public function forget(string $key): bool
    {
        unset($this->items[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->items = [];
        return true;
    }

    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $resolver();
        $this->put($key, $value, $ttlSeconds);

        return $value;
    }

    public function getDriverName(): string
    {
        return 'array';
    }
}

