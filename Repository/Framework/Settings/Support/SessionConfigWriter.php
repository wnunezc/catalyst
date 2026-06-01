<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Framework\Session\SessionManager;
use Catalyst\Helpers\Config\ConfigManager;

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

    private function normalizeTable(string $table): string
    {
        return preg_match('/^[A-Za-z0-9_]+$/', $table) === 1 ? $table : 'sessions';
    }
}
