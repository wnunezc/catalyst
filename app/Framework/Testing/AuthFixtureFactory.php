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

namespace Catalyst\Framework\Testing;

use InvalidArgumentException;
use JsonException;

/**
 * Builds normalized database payloads for authentication fixtures.
 *
 * @package Catalyst\Framework\Testing
 * Responsibility: Converts fixture definitions into persistence-ready user and MFA payloads.
 */
final class AuthFixtureFactory
{
    /**
     * Builds a user insert payload from fixture data.
     *
     * Responsibility: Builds a user insert payload from fixture data.
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
     * Builds an MFA mutation payload for a fixture user.
     *
     * Responsibility: Builds an MFA mutation payload for a fixture user.
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
