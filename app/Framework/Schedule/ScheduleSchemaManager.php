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

namespace Catalyst\Framework\Schedule;

use Catalyst\Framework\Database\DatabaseManager;

/**
 * Defines the Schedule Schema Manager class contract.
 *
 * @package Catalyst\Framework\Schedule
 * Responsibility: Coordinates the schedule schema manager behavior within its module boundary.
 */
final class ScheduleSchemaManager
{
    private static bool $ready = false;

    /**
     * Handles the ensure workflow.
     */
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

    /**
     * Handles the quote workflow.
     */
    private static function quote(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
