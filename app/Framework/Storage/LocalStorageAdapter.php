<?php

declare(strict_types=1);

namespace Catalyst\Framework\Storage;

use Catalyst\Framework\Http\UploadedFile;
use RuntimeException;

final class LocalStorageAdapter implements StorageAdapterInterface
{
    public function __construct(
        private readonly string $rootPath,
        private readonly string $urlPrefix = '/',
        private readonly bool $public = true
    ) {
    }

    public function getDriverName(): string
    {
        return 'local';
    }

    public function put(string $path, string $contents): string
    {
        $normalized = $this->normalizePath($path);
        $absolute = $this->absolutePath($normalized);
        $directory = dirname($absolute);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create storage directory: ' . $directory);
        }

        if (@file_put_contents($absolute, $contents) === false) {
            throw new RuntimeException('Unable to write storage object: ' . $normalized);
        }

        return $normalized;
    }

    public function putFile(UploadedFile $file, string $path): string
    {
        $normalized = $this->normalizePath($path);
        $absolute = $this->absolutePath($normalized);

        $file->moveTo($absolute);

        return $normalized;
    }

    public function get(string $path): string
    {
        $absolute = $this->absolutePath($this->normalizePath($path));

        if (!is_file($absolute)) {
            throw new RuntimeException('Storage object not found: ' . $path);
        }

        $contents = @file_get_contents($absolute);
        if ($contents === false) {
            throw new RuntimeException('Unable to read storage object: ' . $path);
        }

        return $contents;
    }

    public function delete(string $path): bool
    {
        $absolute = $this->absolutePath($this->normalizePath($path));

        return !is_file($absolute) || @unlink($absolute);
    }

    public function exists(string $path): bool
    {
        return is_file($this->absolutePath($this->normalizePath($path)));
    }

    public function url(string $path): string
    {
        if (!$this->public) {
            return '';
        }

        $normalized = str_replace(DIRECTORY_SEPARATOR, '/', $this->normalizePath($path));
        $prefix = rtrim($this->urlPrefix, '/');

        return ($prefix === '' ? '' : $prefix) . '/' . ltrim($normalized, '/');
    }

    private function absolutePath(string $normalizedPath): string
    {
        return rtrim($this->rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
    }

    private function normalizePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        $segments = array_values(array_filter(explode('/', $path), static fn(string $segment): bool => $segment !== ''));

        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..') {
                throw new RuntimeException('Unsafe storage path segment detected.');
            }
        }

        if ($segments === []) {
            throw new RuntimeException('Storage path cannot be empty.');
        }

        return implode('/', $segments);
    }
}
