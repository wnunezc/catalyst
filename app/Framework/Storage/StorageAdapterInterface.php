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

/**
 * Defines the storage operations implemented by framework disks.
 *
 * @package Catalyst\Framework\Storage
 * Responsibility: Standardizes object storage, retrieval, deletion and URL resolution.
 */
interface StorageAdapterInterface
{
    /**
     * Returns the storage driver name.
     *
     * Responsibility: Returns the storage driver name.
     */
    public function getDriverName(): string;

    /**
     * Stores string contents and returns the normalized object path.
     *
     * Responsibility: Stores string contents and returns the normalized object path.
     */
    public function put(string $path, string $contents): string;

    /**
     * Stores an uploaded file and returns the normalized object path.
     *
     * Responsibility: Stores an uploaded file and returns the normalized object path.
     */
    public function putFile(UploadedFile $file, string $path): string;

    /**
     * Reads stored object contents.
     *
     * Responsibility: Reads stored object contents.
     */
    public function get(string $path): string;

    /**
     * Deletes a stored object.
     *
     * Responsibility: Deletes a stored object.
     */
    public function delete(string $path): bool;

    /**
     * Determines whether a stored object exists.
     *
     * Responsibility: Determines whether a stored object exists.
     */
    public function exists(string $path): bool;

    /**
     * Returns the URL for a stored object.
     *
     * Responsibility: Returns the URL for a stored object.
     */
    public function url(string $path): string;
}
