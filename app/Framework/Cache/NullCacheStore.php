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
 * Defines the Null Cache Store class contract.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Coordinates the null cache store behavior within its module boundary.
 */
final class NullCacheStore implements CacheStoreInterface
{
    /**
     * Returns the runtime value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * Handles the put workflow.
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        return false;
    }

    /**
     * Handles the forever workflow.
     */
    public function forever(string $key, mixed $value): bool
    {
        return false;
    }

    /**
     * Handles the has workflow.
     */
    public function has(string $key): bool
    {
        return false;
    }

    /**
     * Handles the forget workflow.
     */
    public function forget(string $key): bool
    {
        return true;
    }

    /**
     * Handles the clear workflow.
     */
    public function clear(): bool
    {
        return true;
    }

    /**
     * Handles the remember workflow.
     */
    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        return $resolver();
    }

    /**
     * Returns the driver name value.
     */
    public function getDriverName(): string
    {
        return 'null';
    }
}

