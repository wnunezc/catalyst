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
 * Defines the Storage Adapter Interface interface contract.
 *
 * @package Catalyst\Framework\Storage
 * Responsibility: Coordinates the storage adapter interface behavior within its module boundary.
 */
interface StorageAdapterInterface
{
    /**
     * Returns the driver name value.
     */
    public function getDriverName(): string;

    /**
     * Handles the put workflow.
     */
    public function put(string $path, string $contents): string;

    /**
     * Handles the put file workflow.
     */
    public function putFile(UploadedFile $file, string $path): string;

    /**
     * Returns the runtime value.
     */
    public function get(string $path): string;

    /**
     * Handles the delete workflow.
     */
    public function delete(string $path): bool;

    /**
     * Handles the exists workflow.
     */
    public function exists(string $path): bool;

    /**
     * Handles the url workflow.
     */
    public function url(string $path): string;
}
