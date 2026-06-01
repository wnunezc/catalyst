<?php

declare(strict_types=1);

namespace Catalyst\Framework\Schedule;

use Catalyst\Framework\Database\DatabaseManager;

final class ScheduleSchemaManager
{
    private static bool $ready = false;

    public static function ensure(): void
    {
        if (self::$ready) {
            return;
        }

        $config = ScheduleSettings::current();
        $table = self::quote((string) $config['history_table']);
        $queueConnection = (string) \Catalyst\Framework\Queue\QueueSettings::current()['connection'];
        $pdo = DatabaseManager::getInstance()->connection($queueConnection)->getPdo();

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS {$table} (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                task_name VARCHAR(191) NOT NULL,
                expression VARCHAR(64) NOT NULL,
                slot_key VARCHAR(32) NOT NULL,
                queue_name VARCHAR(64) NOT NULL,
                status VARCHAR(32) NOT NULL,
                queued_job_id BIGINT UNSIGNED NULL,
                message VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                finished_at DATETIME NULL,
                UNIQUE KEY uq_scheduler_slot (task_name, slot_key),
                INDEX idx_scheduler_status (status),
                INDEX idx_scheduler_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        self::$ready = true;
    }

    private static function quote(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
