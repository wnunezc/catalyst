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

namespace Catalyst\Helpers\Security;

/**
 * Redacts sensitive values from nested payloads.
 *
 * @package Catalyst\Helpers\Security
 * Responsibility: Detects sensitive key names and replaces their values before exposure.
 */
final class SensitiveValueRedactor
{
    public const REDACTED = '[REDACTED]';

    /**
     * @var string[]
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'csrf_token',
        'new_token',
        'token',
        'token_received',
        'token_received_to_validate',
        'session_tokens',
        'session_id',
        'session_values',
        'authorization',
        'cookie',
        'set-cookie',
        'secret',
        'app_key',
        'project_key',
        'mail_password',
        'db_password',
        'ftp_password',
        'mfa_secret',
        'remember_token',
        'reset_token',
        'oauth_token',
        'refresh_token',
        'private_key',
        'client_secret',
        'google_client_secret',
        'github_client_secret',
        'x-csrf-token',
    ];

    /**
     * @var string[]
     */
    private const SENSITIVE_NEEDLES = [
        'token',
        'secret',
        'password',
        'cookie',
        'authorization',
        'key',
    ];

    /**
     * Recursively replaces sensitive payload values with the redaction marker.
     *
     * Responsibility: Recursively replaces sensitive payload values with the redaction marker.
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function sanitize(array $payload): array
    {
        $sanitized = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = is_string($key) ? strtolower($key) : (string) $key;

            if ($this->isSensitiveKey($normalizedKey)) {
                $sanitized[$key] = self::REDACTED;
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * Determines whether a key name indicates sensitive data.
     *
     * Responsibility: Determines whether a key name indicates sensitive data.
     */
    public function isSensitiveKey(string $key): bool
    {
        if (in_array($key, self::SENSITIVE_KEYS, true)) {
            return true;
        }

        foreach (self::SENSITIVE_NEEDLES as $needle) {
            if (str_contains($key, $needle)) {
                return true;
            }
        }

        return false;
    }
}
