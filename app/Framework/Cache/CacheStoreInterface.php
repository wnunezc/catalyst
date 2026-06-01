<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cache;

interface CacheStoreInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool;

    public function forever(string $key, mixed $value): bool;

    public function has(string $key): bool;

    public function forget(string $key): bool;

    public function clear(): bool;

    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed;

    public function getDriverName(): string;
}

