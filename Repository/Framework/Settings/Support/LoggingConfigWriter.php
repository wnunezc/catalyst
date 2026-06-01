<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

final class LoggingConfigWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        ConfigManager::getInstance()->writeSection('logging', [
            'logging' => [
                'log_channel' => (string) ($data['log_channel'] ?? 'single'),
                'log_level' => (string) ($data['log_level'] ?? 'warning'),
                'display_logs' => (bool) ($data['display_logs'] ?? false),
                'log_rotation_enabled' => (bool) ($data['log_rotation_enabled'] ?? true),
                'log_max_file_size_mb' => min(50, max(1, (int) ($data['log_max_file_size_mb'] ?? 2))),
                'log_max_rotated_files' => min(10, max(1, (int) ($data['log_max_rotated_files'] ?? 5))),
            ],
        ]);
    }
}