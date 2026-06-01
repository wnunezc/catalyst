<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
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