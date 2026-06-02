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
 * Defines the File Cache Store class contract.
 *
 * @package Catalyst\Framework\Cache
 * Responsibility: Coordinates the file cache store behavior within its module boundary.
 */
final class FileCacheStore implements CacheStoreInterface
{
    /**
     * Initializes the File Cache Store instance.
     */
    public function __construct(
        private readonly string $baseDirectory,
        private readonly string $prefix = 'catalyst_'
    ) {
    }

    /**
     * Returns the runtime value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $payload = $this->readPayload($key);

        return $payload['hit'] ? $payload['value'] : $default;
    }

    /**
     * Handles the put workflow.
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
     * Handles the forever workflow.
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Handles the has workflow.
     */
    public function has(string $key): bool
    {
        return $this->readPayload($key)['hit'];
    }

    /**
     * Handles the forget workflow.
     */
    public function forget(string $key): bool
    {
        $path = $this->pathForKey($key);

        return !file_exists($path) || @unlink($path);
    }

    /**
     * Handles the clear workflow.
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
     * Handles the remember workflow.
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
     * Returns the driver name value.
     */
    public function getDriverName(): string
    {
        return 'file';
    }

    /**
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
     * Handles the namespace directory workflow.
     */
    private function namespaceDirectory(): string
    {
        $prefix = preg_replace('/[^a-zA-Z0-9_-]+/', '_', trim($this->prefix)) ?: 'catalyst_';

        return rtrim($this->baseDirectory, '\\/') . DS . $prefix;
    }

    /**
     * Handles the path for key workflow.
     */
    private function pathForKey(string $key): string
    {
        $normalized = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $key) ?: 'cache_key';
        $hash = sha1($key);

        return $this->namespaceDirectory() . DS . $normalized . '_' . $hash . '.php';
    }
}
