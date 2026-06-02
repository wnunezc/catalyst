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

use Catalyst\Framework\Security\SignedSerializedPayload;

/**
 * Persists signed cache entries as PHP files.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Reads, writes and evicts namespaced filesystem cache entries.
 */
final class FileCacheStore implements CacheStoreInterface
{
    /**
     * Initializes the File Cache Store instance.
     *
     * Responsibility: Initializes the File Cache Store instance.
     */
    public function __construct(
        private readonly string $baseDirectory,
        private readonly string $prefix = 'catalyst_'
    ) {
    }

    /**
     * Returns a verified filesystem cache value or the supplied default.
     *
     * Responsibility: Returns a verified filesystem cache value or the supplied default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $payload = $this->readPayload($key);

        return $payload['hit'] ? $payload['value'] : $default;
    }

    /**
     * Atomically stores a signed filesystem cache value.
     *
     * Responsibility: Atomically stores a signed filesystem cache value.
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        $directory = $this->namespaceDirectory();
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return false;
        }

        $expiresAt = $ttlSeconds > 0 ? (time() + $ttlSeconds) : null;
        $payload = [
            'expires_at' => $expiresAt,
            ...SignedSerializedPayload::pack($value),
        ];

        $encoded = '<?php return ' . var_export($payload, true) . ';';
        $target = $this->pathForKey($key);
        $temp = $target . '.' . bin2hex(random_bytes(4)) . '.tmp';

        if (file_put_contents($temp, $encoded, LOCK_EX) === false) {
            @unlink($temp);
            return false;
        }

        if (!@rename($temp, $target)) {
            @unlink($temp);
            return false;
        }

        return true;
    }

    /**
     * Stores a filesystem cache value without expiration.
     *
     * Responsibility: Stores a filesystem cache value without expiration.
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Determines whether a verified filesystem cache entry exists.
     *
     * Responsibility: Determines whether a verified filesystem cache entry exists.
     */
    public function has(string $key): bool
    {
        return $this->readPayload($key)['hit'];
    }

    /**
     * Removes one filesystem cache entry.
     *
     * Responsibility: Removes one filesystem cache entry.
     */
    public function forget(string $key): bool
    {
        $path = $this->pathForKey($key);

        return !file_exists($path) || @unlink($path);
    }

    /**
     * Removes every PHP cache file in the configured namespace.
     *
     * Responsibility: Removes every PHP cache file in the configured namespace.
     */
    public function clear(): bool
    {
        $directory = $this->namespaceDirectory();
        if (!is_dir($directory)) {
            return true;
        }

        $success = true;

        foreach (glob($directory . DS . '*.php') ?: [] as $file) {
            if (is_file($file) && !@unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Returns a cached value or stores the resolver result.
     *
     * Responsibility: Returns a cached value or stores the resolver result.
     */
    public function remember(string $key, callable $resolver, int $ttlSeconds = 0): mixed
    {
        $payload = $this->readPayload($key);
        if ($payload['hit']) {
            return $payload['value'];
        }

        $value = $resolver();
        $this->put($key, $value, $ttlSeconds);

        return $value;
    }

    /**
     * Returns the file driver name.
     *
     * Responsibility: Returns the file driver name.
     */
    public function getDriverName(): string
    {
        return 'file';
    }

    /**
     * Reads and validates a signed filesystem cache payload.
     *
     * Responsibility: Reads and validates a signed filesystem cache payload.
     * @return array{hit:bool,value:mixed}
     */
    private function readPayload(string $key): array
    {
        $path = $this->pathForKey($key);
        if (!is_file($path)) {
            return ['hit' => false, 'value' => null];
        }

        $payload = require $path;
        if (!is_array($payload)) {
            @unlink($path);
            return ['hit' => false, 'value' => null];
        }

        $expiresAt = $payload['expires_at'] ?? null;
        if (is_int($expiresAt) && $expiresAt < time()) {
            @unlink($path);
            return ['hit' => false, 'value' => null];
        }

        $decoded = SignedSerializedPayload::unpack($payload);
        if (!$decoded['valid']) {
            @unlink($path);
            return ['hit' => false, 'value' => null];
        }

        return [
            'hit' => true,
            'value' => $decoded['value'],
        ];
    }

    /**
     * Returns the sanitized cache namespace directory.
     *
     * Responsibility: Returns the sanitized cache namespace directory.
     */
    private function namespaceDirectory(): string
    {
        $prefix = preg_replace('/[^a-zA-Z0-9_-]+/', '_', trim($this->prefix)) ?: 'catalyst_';

        return rtrim($this->baseDirectory, '\\/') . DS . $prefix;
    }

    /**
     * Builds the cache file path for a logical key.
     *
     * Responsibility: Builds the cache file path for a logical key.
     */
    private function pathForKey(string $key): string
    {
        $normalized = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $key) ?: 'cache_key';
        $hash = sha1($key);

        return $this->namespaceDirectory() . DS . $normalized . '_' . $hash . '.php';
    }
}
