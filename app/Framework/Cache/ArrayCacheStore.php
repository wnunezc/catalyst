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
 * Stores cache entries in process memory.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Provides ephemeral cache storage with optional expiration for the current process.
 */
final class ArrayCacheStore implements CacheStoreInterface
{
    /** @var array<string, array{expires_at:int|null,value:mixed}> */
    private array $items = [];

    /**
     * Returns an in-memory cached value or the supplied default.
     *
     * Responsibility: Returns an in-memory cached value or the supplied default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->items[$key]['value'] ?? $default;
    }

    /**
     * Stores an in-memory value with an optional time-to-live.
     *
     * Responsibility: Stores an in-memory value with an optional time-to-live.
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        $this->items[$key] = [
            'expires_at' => $ttlSeconds > 0 ? (time() + $ttlSeconds) : null,
            'value' => $value,
        ];

        return true;
    }

    /**
     * Stores an in-memory value without expiration.
     *
     * Responsibility: Stores an in-memory value without expiration.
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value);
    }

    /**
     * Determines whether a non-expired in-memory entry exists.
     *
     * Responsibility: Determines whether a non-expired in-memory entry exists.
     */
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

    /**
     * Removes one in-memory entry.
     *
     * Responsibility: Removes one in-memory entry.
     */
    public function forget(string $key): bool
    {
        unset($this->items[$key]);
        return true;
    }

    /**
     * Removes every in-memory entry.
     *
     * Responsibility: Removes every in-memory entry.
     */
    public function clear(): bool
    {
        $this->items = [];
        return true;
    }

    /**
     * Returns an existing entry or stores the resolver result.
     *
     * Responsibility: Returns an existing entry or stores the resolver result.
     */
    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $resolver();
        $this->put($key, $value, $ttlSeconds);

        return $value;
    }

    /**
     * Returns the array driver name.
     *
     * Responsibility: Returns the array driver name.
     */
    public function getDriverName(): string
    {
        return 'array';
    }
}
