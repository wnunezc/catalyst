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
