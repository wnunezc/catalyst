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

namespace Catalyst\Helpers\Exceptions;

use RuntimeException;

/**
 * Exception class for filed system-related errors
 *
 * Provides factory methods for common file system error scenarios
 *
 * @package Catalyst\Framework\Exceptions
 */
class FileSystemException extends RuntimeException
{

    /**
     * @param string $path
     * @param string|null $reason
     * @return self
     */
    public static function unableToWriteFile(string $path, ?string $reason = null): self
    {
        $message = "Unable to write to file: '$path'";
        if ($reason) {
            $message .= " - Reason: $reason";
        }
        return new self($message);
    }

    /**
     * @param string $path
     * @param string|null $reason
     * @return self
     */
    public static function unableToReadFile(string $path, ?string $reason = null): self
    {
        $message = "Unable to read file: '$path'";
        if ($reason) {
            $message .= " - Reason: $reason";
        }
        return new self($message);
    }

    /**
     * @param string $path
     * @return self
     */
    public static function fileMissing(string $path): self
    {
        return new self("Required file not found: '$path'");
    }
}