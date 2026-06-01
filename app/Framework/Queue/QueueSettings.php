<?php

declare(strict_types=1);

namespace Catalyst\Framework\Queue;

use Catalyst\Helpers\Config\ConfigManager;

final class QueueSettings
{
    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'connection' => 'db1',
            'default_queue' => 'default',
            'jobs_table' => 'queue_jobs',
            'failed_jobs_table' => 'failed_jobs',
            'stale_after_seconds' => 300,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function current(): array
    {
        $config = ConfigManager::getInstance()->entry('queue', 'queue', self::defaults());

        $config['enabled'] = (bool) ($config['enabled'] ?? true);
        $config['connection'] = trim((string) ($config['connection'] ?? 'db1')) ?: 'db1';
        $config['default_queue'] = trim((string) ($config['default_queue'] ?? 'default')) ?: 'default';
        $config['jobs_table'] = trim((string) ($config['jobs_table'] ?? 'queue_jobs')) ?: 'queue_jobs';
        $config['failed_jobs_table'] = trim((string) ($config['failed_jobs_table'] ?? 'failed_jobs')) ?: 'failed_jobs';
        $config['stale_after_seconds'] = max(30, (int) ($config['stale_after_seconds'] ?? 300));

        return $config;
    }
}
