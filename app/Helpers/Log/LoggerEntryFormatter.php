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

namespace Catalyst\Helpers\Log;

/**
 * Formats structured application and email log entries.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Adds request metadata, timestamps, client identity and serialized context to log messages.
 */
final class LoggerEntryFormatter
{
    /**
     * Formats an application log entry with request metadata when available.
     *
     * Responsibility: Formats an application log entry with request metadata when available.
     */
    public function format(string $level, string $message, array $context, string $requestId): string
    {
        if (!IS_CLI && !isset($context['request_metadata'])) {
            $context['request_metadata'] = [
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'referer' => $_SERVER['HTTP_REFERER'] ?? 'direct',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'request_id' => $requestId,
            ];
        }

        return $this->buildEntry($level, $message, $context);
    }

    /**
     * Formats an email log entry.
     *
     * Responsibility: Formats an email log entry.
     */
    public function formatEmail(string $message, array $context): string
    {
        return $this->buildEntry('EMAIL', $message, $context);
    }

    /**
     * Builds the common serialized log entry representation.
     *
     * Responsibility: Builds the common serialized log entry representation.
     */
    private function buildEntry(string $level, string $message, array $context): string
    {
        $logEntry = sprintf(
            '[%s] [%s] [%s] [User:%s] %s',
            date('Y-m-d H:i:s'),
            $level,
            $this->getClientIp(),
            $this->getCurrentUserId(),
            $message
        );

        if (!empty($context)) {
            $logEntry .= ' ' . json_encode($context);
        }

        return $logEntry;
    }

    /**
     * Returns the authenticated user identifier or the guest marker.
     *
     * Responsibility: Returns the authenticated user identifier or the guest marker.
     */
    private function getCurrentUserId(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return 'guest';
        }

        return (string) ($_SESSION['user_id'] ?? 'guest');
    }

    /**
     * Returns the client IP address or CLI marker.
     *
     * Responsibility: Returns the client IP address or CLI marker.
     */
    private function getClientIp(): string
    {
        if (IS_CLI) {
            return 'CLI';
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
}
