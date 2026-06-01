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
 * Exception thrown when the environment configuration cannot be loaded.
 *
 * This exception is raised during the bootstrap phase when the .env file
 * is missing, unreadable, empty, or contains an invalid configuration.
 * It is caught by the bootstrap handler which exits gracefully — application
 * code should never need to catch this exception.
 *
 * @package Catalyst\Helpers\Exceptions
 */
class EnvironmentException extends RuntimeException
{
    /**
     * @param string $path Expected .env file path
     * @return self
     */
    public static function fileMissing(string $path): self
    {
        return new self("Required .env file not found: '$path'");
    }

    /**
     * @param string $source Source .env.example path
     * @param string $dest   Destination .env path
     * @return self
     */
    public static function copyFailed(string $source, string $dest): self
    {
        return new self("Unable to copy '$source' to '$dest'");
    }

    /**
     * @param string $path .env file path
     * @return self
     */
    public static function unreadable(string $path): self
    {
        return new self("Unable to read environment file: '$path'");
    }

    /**
     * @param string $path .env file path
     * @return self
     */
    public static function empty(string $path): self
    {
        return new self("Environment file is empty: '$path'");
    }
}
