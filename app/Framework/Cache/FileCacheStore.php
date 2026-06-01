<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cache;

use Catalyst\Framework\Security\SignedSerializedPayload;

final class FileCacheStore implements CacheStoreInterface
{
    public function __construct(
        private readonly string $baseDirectory,
        private readonly string $prefix = 'catalyst_'
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $payload = $this->readPayload($key);

        return $payload['hit'] ? $payload['value'] : $default;
    }

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

    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, 0);
    }

    public function has(string $key): bool
    {
        return $this->readPayload($key)['hit'];
    }

    public function forget(string $key): bool
    {
        $path = $this->pathForKey($key);

        return !file_exists($path) || @unlink($path);
    }

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

    private function namespaceDirectory(): string
    {
        $prefix = preg_replace('/[^a-zA-Z0-9_-]+/', '_', trim($this->prefix)) ?: 'catalyst_';

        return rtrim($this->baseDirectory, '\\/') . DS . $prefix;
    }

    private function pathForKey(string $key): string
    {
        $normalized = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $key) ?: 'cache_key';
        $hash = sha1($key);

        return $this->namespaceDirectory() . DS . $normalized . '_' . $hash . '.php';
    }
}
