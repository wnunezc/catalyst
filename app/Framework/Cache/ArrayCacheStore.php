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
 * Defines the Array Cache Store class contract.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Coordinates the array cache store behavior within its module boundary.
 */
final class ArrayCacheStore implements CacheStoreInterface
{
    /** @var array<string, array{expires_at:int|null,value:mixed}> */
    private array $items = [];

    /**
     * Returns the runtime value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->items[$key]['value'] ?? $default;
    }

    /**
     * Handles the put workflow.
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
     * Handles the forever workflow.
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value);
    }

    /**
     * Handles the has workflow.
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
     * Handles the forget workflow.
     */
    public function forget(string $key): bool
    {
        unset($this->items[$key]);
        return true;
    }

    /**
     * Handles the clear workflow.
     */
    public function clear(): bool
    {
        $this->items = [];
        return true;
    }

    /**
     * Handles the remember workflow.
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
     * Returns the driver name value.
     */
    public function getDriverName(): string
    {
        return 'array';
    }
}

