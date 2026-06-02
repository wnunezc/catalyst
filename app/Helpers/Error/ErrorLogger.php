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

namespace Catalyst\Helpers\Error;

use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Class to handle logging of errors caught by BugCatcher
 *
 * @package Catalyst\Helpers\Error;
 */
class ErrorLogger
{
    /**
     * Log an error caught by BugCatcher
     *
     * @param array $errorData Error information from BugCatcher
     * @return void
     * @throws Exception
     */
    public static function logError(array $errorData): void
    {
        $logger = Logger::getInstance();

        $level = self::determineLogLevel($errorData['type'] ?? 'UNKNOWN');

        // Create the error message
        $message = sprintf(
            '%s: %s in %s on line %d',
            $errorData['class'] ?? 'Unknown Error',
            $errorData['description'] ?? 'No description',
            $errorData['file'] ?? 'unknown file',
            $errorData['line'] ?? 0
        );

        // Add context data
        $context = [
            'ticket'     => $errorData['micro_time'] ?? null,
            'error_type' => $errorData['type'] ?? 'Unknown',
            'class'      => $errorData['class'] ?? 'Unknown',
            'file'       => $errorData['file'] ?? 'Unknown',
            'line'       => $errorData['line'] ?? 0,
            'trace'      => $errorData['trace_msg'] ?? 'No trace available',
        ];

        // Log the error
        $logger->log($level, $message, $context);
    }

    /**
     * Determine the appropriate log level based on a PHP error type
     *
     * @param int|string $errorType PHP error type constant or string error name
     * @return string Log level
     */
    private static function determineLogLevel(int|string $errorType): string
    {
        // Handle string error types
        if (is_string($errorType)) {
            return match (strtoupper($errorType)) {
                'FATAL ERROR', 'ERROR', 'PARSE', 'COMPILE_ERROR' => 'ERROR',
                'WARNING', 'CORE_WARNING', 'COMPILE_WARNING', 'USER_WARNING' => 'WARNING',
                'NOTICE', 'USER_NOTICE' => 'NOTICE',
                'DEPRECATED', 'USER_DEPRECATED' => 'INFO',
                default => 'ERROR' // Default to ERROR for unknown string types
            };
        }

        // Handle integer error types (original logic)
        return match ($errorType) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR => 'ERROR',
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => 'WARNING',
            E_NOTICE, E_USER_NOTICE => 'NOTICE',
            E_DEPRECATED, E_USER_DEPRECATED => 'INFO',
            default => 'DEBUG'
        };
    }
}
