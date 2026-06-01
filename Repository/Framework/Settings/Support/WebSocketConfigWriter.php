<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

final class WebSocketConfigWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        ConfigManager::getInstance()->writeSection('websocket', [
            'websocket' => [
                'enabled' => (bool) ($data['ws_enabled'] ?? true),
                'ws_port' => (int) ($data['ws_port'] ?? 8080),
                'ws_host' => (string) ($data['ws_host'] ?? '0.0.0.0'),
                'ws_internal_port' => (int) ($data['ws_internal_port'] ?? 8181),
                'ws_publisher_url' => (string) ($data['ws_publisher_url'] ?? ''),
            ],
        ]);
    }
}
