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

namespace Catalyst\Repository\Configuration\Support;

use Catalyst\Framework\Session\SessionManager;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Writes session settings and refreshes the active session metadata.
 *
 * @package Catalyst\Repository\Configuration\Support
 * Responsibility: Persists storage and cookie settings and seeds the current session with its active backend.
 */
final class SessionConfigWriter
{
    /**
     * Saves normalized session settings and updates the active session backend metadata.
     *
     * Responsibility: Saves normalized session settings and updates the active session backend metadata.
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        $payload = [
            'session' => [
                'session_driver' => (string) ($data['session_driver'] ?? 'file'),
                'session_connection' => (string) (($data['session_connection'] ?? '') !== '' ? $data['session_connection'] : 'db1'),
                'session_table' => $this->normalizeTable((string) ($data['session_table'] ?? 'sessions')),
                'session_name' => (string) ($data['session_name'] ?? 'catalyst-session'),
                'session_lifetime' => (int) ($data['session_lifetime'] ?? 2592000),
                'session_secure' => (bool) ($data['session_secure'] ?? true),
                'session_http_only' => (bool) ($data['session_http_only'] ?? true),
                'session_same_site' => (string) ($data['session_same_site'] ?? 'Strict'),
                'session_domain' => (string) ($data['session_domain'] ?? ''),
            ],
        ];

        ConfigManager::getInstance()->writeSection('session', $payload);

        SessionManager::getInstance()->seedActiveSession([
            'driver' => $payload['session']['session_driver'],
            'connection' => $payload['session']['session_connection'],
            'table' => $payload['session']['session_table'],
        ]);
    }

    /**
     * Accepts a safe session table name or falls back to the default.
     *
     * Responsibility: Accepts a safe session table name or falls back to the default.
     */
    private function normalizeTable(string $table): string
    {
        return preg_match('/^[A-Za-z0-9_]+$/', $table) === 1 ? $table : 'sessions';
    }
}
