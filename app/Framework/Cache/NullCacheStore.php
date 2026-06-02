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
 * Implements a cache store that intentionally persists nothing.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Disables caching while preserving the cache store contract.
 */
final class NullCacheStore implements CacheStoreInterface
{
    /**
     * Returns the supplied default because no values are stored.
     *
     * Responsibility: Returns the supplied default because no values are stored.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * Rejects storage because the null driver is disabled.
     *
     * Responsibility: Rejects storage because the null driver is disabled.
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        return false;
    }

    /**
     * Rejects non-expiring storage because the null driver is disabled.
     *
     * Responsibility: Rejects non-expiring storage because the null driver is disabled.
     */
    public function forever(string $key, mixed $value): bool
    {
        return false;
    }

    /**
     * Reports that no cached value exists.
     *
     * Responsibility: Reports that no cached value exists.
     */
    public function has(string $key): bool
    {
        return false;
    }

    /**
     * Reports successful eviction because no cached value exists.
     *
     * Responsibility: Reports successful eviction because no cached value exists.
     */
    public function forget(string $key): bool
    {
        return true;
    }

    /**
     * Reports successful clearing because the store is empty.
     *
     * Responsibility: Reports successful clearing because the store is empty.
     */
    public function clear(): bool
    {
        return true;
    }

    /**
     * Resolves and returns the value without caching it.
     *
     * Responsibility: Resolves and returns the value without caching it.
     */
    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        return $resolver();
    }

    /**
     * Returns the null driver name.
     *
     * Responsibility: Returns the null driver name.
     */
    public function getDriverName(): string
    {
        return 'null';
    }
}
