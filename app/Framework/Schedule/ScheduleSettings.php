<?php

declare(strict_types=1);

namespace Catalyst\Framework\Schedule;

use Catalyst\Helpers\Config\ConfigManager;

final class ScheduleSettings
{
    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'history_table' => 'scheduler_runs',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function current(): array
    {
        $config = ConfigManager::getInstance()->entry('schedule', 'schedule', self::defaults());

        $config['enabled'] = (bool) ($config['enabled'] ?? true);
        $config['history_table'] = trim((string) ($config['history_table'] ?? 'scheduler_runs')) ?: 'scheduler_runs';

        return $config;
    }
}
