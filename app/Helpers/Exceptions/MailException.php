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

use Exception;

/**
 * Mail exception
 *
 * Handles mail-related errors with specific error codes and messages.
 *
 * @package Catalyst\Helpers\Exceptions
 * Responsibility: Provides typed error codes and factories for mail delivery failures.
 */
class MailException extends Exception
{
    // Error codes
    public const int ERROR_CONFIGURATION = 100;
    public const int ERROR_INVALID_ADDRESS = 101;
    public const int ERROR_SENDING = 102;
    public const int ERROR_ATTACHMENT = 103;
    public const int ERROR_TEMPLATE = 104;
    public const int ERROR_DKIM = 105;

    /**
     * Create a new configuration error instance
     *
     * @param string $message Error message
     * @return static
     */
    public static function configurationError(string $message): self
    {
        return new static($message, self::ERROR_CONFIGURATION);
    }

    /**
     * Create a new invalid address error instance
     *
     * @param string $address Invalid email address
     * @return static
     */
    public static function invalidAddress(string $address): self
    {
        return new static("Invalid email address: $address", self::ERROR_INVALID_ADDRESS);
    }

    /**
     * Create a new sending error instance
     *
     * @param string $message Error message
     * @return static
     */
    public static function sendingError(string $message): self
    {
        return new static("Failed to send email: $message", self::ERROR_SENDING);
    }

    /**
     * Create a new attachment error instance
     *
     * @param string $filePath File path
     * @param string $message Error message
     * @return static
     */
    public static function attachmentError(string $filePath, string $message): self
    {
        return new static("Failed to attach file '$filePath': $message", self::ERROR_ATTACHMENT);
    }

    /**
     * Create a new template error instance
     *
     * @param string $template MailTemplate name or path
     * @param string $message Error message
     * @return static
     */
    public static function templateError(string $template, string $message): self
    {
        return new static("Failed to process template '$template': $message", self::ERROR_TEMPLATE);
    }

    /**
     * Create a new DKIM error instance
     *
     * @param string $message Error message
     * @return static
     */
    public static function dkimError(string $message): self
    {
        return new static("DKIM signing error: $message", self::ERROR_DKIM);
    }
}
