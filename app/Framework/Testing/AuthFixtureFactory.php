<?php

declare(strict_types=1);

namespace Catalyst\Framework\Testing;

use InvalidArgumentException;
use JsonException;

final class AuthFixtureFactory
{
    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    public function makeUserInsertPayload(array $user): array
    {
        return [
            'id' => (int) ($user['id'] ?? 0),
            'name' => (string) ($user['name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'password' => (string) ($user['password'] ?? ''),
            'active' => (int) ($user['active'] ?? 1),
            'email_verified' => (int) ($user['email_verified'] ?? 0),
            'last_login' => $user['last_login'] ?? null,
            'mfa_secret' => $user['mfa_secret'] ?? null,
            'mfa_enabled' => (int) ($user['mfa_enabled'] ?? 0),
            'mfa_backup_codes' => isset($user['mfa_backup_codes'])
                ? json_encode($user['mfa_backup_codes'], JSON_THROW_ON_ERROR)
                : null,
            'created_at' => (string) ($user['created_at'] ?? date('Y-m-d H:i:s')),
            'updated_at' => (string) ($user['updated_at'] ?? date('Y-m-d H:i:s')),
        ];
    }

    /**
     * @param array<string, mixed> $fixture
     * @return array<string, mixed>
     */
    public function makeMfaMutationPayload(array $fixture, bool $enabled): array
    {
        $payload = [
            'mfa_enabled' => $enabled ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!$enabled) {
            $payload['mfa_secret'] = null;
            $payload['mfa_backup_codes'] = null;

            return $payload;
        }

        $secret = trim((string) ($fixture['mfa_secret'] ?? ''));
        if ($secret === '') {
            throw new InvalidArgumentException('Auth fixture catalog does not define an MFA secret for user: ' . (string) ($fixture['key'] ?? 'unknown'));
        }

        try {
            $backupCodes = json_encode(
                array_values((array) ($fixture['mfa_backup_codes'] ?? [])),
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new InvalidArgumentException('Unable to encode MFA backup codes for fixture user: ' . (string) ($fixture['key'] ?? 'unknown'), 0, $e);
        }

        $payload['mfa_secret'] = $secret;
        $payload['mfa_backup_codes'] = $backupCodes;

        return $payload;
    }
}
