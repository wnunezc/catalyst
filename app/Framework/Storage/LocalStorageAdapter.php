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
 * Defines the Local Storage Adapter class contract.
 *
 * @package Catalyst\Framework\Storage
 * Responsibility: Coordinates the local storage adapter behavior within its module boundary.
 */
final class LocalStorageAdapter implements StorageAdapterInterface
{
    /**
     * Initializes the Local Storage Adapter instance.
     */
    public function __construct(
        private readonly string $rootPath,
        private readonly string $urlPrefix = '/',
        private readonly bool $public = true
    ) {
    }

    /**
     * Returns the driver name value.
     */
    public function getDriverName(): string
    {
        return 'local';
    }

    /**
     * Handles the put workflow.
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
     * Handles the put file workflow.
     */
    public function putFile(UploadedFile $file, string $path): string
    {
        $normalized = $this->normalizePath($path);
        $absolute = $this->absolutePath($normalized);

        $file->moveTo($absolute);

        return $normalized;
    }

    /**
     * Returns the runtime value.
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
     * Handles the delete workflow.
     */
    public function delete(string $path): bool
    {
        $absolute = $this->absolutePath($this->normalizePath($path));

        return !is_file($absolute) || @unlink($absolute);
    }

    /**
     * Handles the exists workflow.
     */
    public function exists(string $path): bool
    {
        return is_file($this->absolutePath($this->normalizePath($path)));
    }

    /**
     * Handles the url workflow.
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
     * Handles the absolute path workflow.
     */
    private function absolutePath(string $normalizedPath): string
    {
        return rtrim($this->rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
    }

    /**
     * Normalizes the provided value.
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
