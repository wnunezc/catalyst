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

namespace Catalyst\Framework\Storage;

use Catalyst\Framework\Http\UploadedFile;
use RuntimeException;

/**
 * Stores objects beneath a configured local filesystem root.
 *
 * @package Catalyst\Framework\Storage
 * Responsibility: Provides normalized local file persistence and optional public URLs.
 */
final class LocalStorageAdapter implements StorageAdapterInterface
{
    /**
     * Initializes the Local Storage Adapter instance.
     *
     * Responsibility: Initializes the Local Storage Adapter instance.
     */
    public function __construct(
        private readonly string $rootPath,
        private readonly string $urlPrefix = '/',
        private readonly bool $public = true
    ) {
    }

    /**
     * Returns the local storage driver name.
     *
     * Responsibility: Returns the local storage driver name.
     */
    public function getDriverName(): string
    {
        return 'local';
    }

    /**
     * Writes string contents to a local object path.
     *
     * Responsibility: Writes string contents to a local object path.
     */
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

    /**
     * Moves an uploaded file to a local object path.
     *
     * Responsibility: Moves an uploaded file to a local object path.
     */
    public function putFile(UploadedFile $file, string $path): string
    {
        $normalized = $this->normalizePath($path);
        $absolute = $this->absolutePath($normalized);

        $file->moveTo($absolute);

        return $normalized;
    }

    /**
     * Reads contents from a local object path.
     *
     * Responsibility: Reads contents from a local object path.
     */
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

    /**
     * Deletes a local object when present.
     *
     * Responsibility: Deletes a local object when present.
     */
    public function delete(string $path): bool
    {
        $absolute = $this->absolutePath($this->normalizePath($path));

        return !is_file($absolute) || @unlink($absolute);
    }

    /**
     * Determines whether a local object exists.
     *
     * Responsibility: Determines whether a local object exists.
     */
    public function exists(string $path): bool
    {
        return is_file($this->absolutePath($this->normalizePath($path)));
    }

    /**
     * Returns a public URL when this disk is exposed.
     *
     * Responsibility: Returns a public URL when this disk is exposed.
     */
    public function url(string $path): string
    {
        if (!$this->public) {
            return '';
        }

        $normalized = str_replace(DIRECTORY_SEPARATOR, '/', $this->normalizePath($path));
        $prefix = rtrim($this->urlPrefix, '/');

        return ($prefix === '' ? '' : $prefix) . '/' . ltrim($normalized, '/');
    }

    /**
     * Resolves a normalized object path beneath the storage root.
     *
     * Responsibility: Resolves a normalized object path beneath the storage root.
     */
    private function absolutePath(string $normalizedPath): string
    {
        return rtrim($this->rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
    }

    /**
     * Normalizes an object path and rejects traversal segments.
     *
     * Responsibility: Normalizes an object path and rejects traversal segments.
     */
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
