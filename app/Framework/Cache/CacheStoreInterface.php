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
 * Defines the operations required from a runtime cache store.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Standardizes cache reads, writes, eviction and resolver-backed retrieval.
 */
interface CacheStoreInterface
{
    /**
     * Returns a cached value or the supplied default.
     *
     * Responsibility: Returns a cached value or the supplied default.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Stores a value with an optional time-to-live.
     *
     * Responsibility: Stores a value with an optional time-to-live.
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool;

    /**
     * Stores a value without expiration.
     *
     * Responsibility: Stores a value without expiration.
     */
    public function forever(string $key, mixed $value): bool;

    /**
     * Determines whether a usable cache entry exists.
     *
     * Responsibility: Determines whether a usable cache entry exists.
     */
    public function has(string $key): bool;

    /**
     * Removes one cache entry.
     *
     * Responsibility: Removes one cache entry.
     */
    public function forget(string $key): bool;

    /**
     * Removes all entries managed by the store.
     *
     * Responsibility: Removes all entries managed by the store.
     */
    public function clear(): bool;

    /**
     * Returns a cached value or stores the resolver result.
     *
     * Responsibility: Returns a cached value or stores the resolver result.
     */
    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed;

    /**
     * Returns the cache driver name.
     *
     * Responsibility: Returns the cache driver name.
     */
    public function getDriverName(): string;
}
