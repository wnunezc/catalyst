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

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Framework\Session\SessionManager;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Defines the Session Config Writer class contract.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Coordinates the session config writer behavior within its module boundary.
 */
final class SessionConfigWriter
{
    /**
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
     * Normalizes the provided value.
     */
    private function normalizeTable(string $table): string
    {
        return preg_match('/^[A-Za-z0-9_]+$/', $table) === 1 ? $table : 'sessions';
    }
}
