<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Security;

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
