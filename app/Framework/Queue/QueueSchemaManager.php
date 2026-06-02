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

namespace Catalyst\Framework\Queue;

use Catalyst\Framework\Database\DatabaseManager;
use PDO;

/**
 * Ensures the queue storage tables exist before repository operations run.
 *
 * @package Catalyst\Framework\Queue
 * Responsibility: Creates pending and failed queue tables once per request using the configured database connection.
 */
final class QueueSchemaManager
{
    private static bool $ready = false;

    /**
     * Creates the queue tables when they have not been initialized yet.
     */
    public static function ensure(): void
    {
        if (self::$ready) {
            return;
        }

        $config = QueueSettings::current();
        $pdo = DatabaseManager::getInstance()->connection((string) $config['connection'])->getPdo();
        $jobsTable = self::quote((string) $config['jobs_table']);
        $failedTable = self::quote((string) $config['failed_jobs_table']);

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS {$jobsTable} (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                queue_name VARCHAR(64) NOT NULL,
                job_class VARCHAR(191) NOT NULL,
                display_name VARCHAR(191) NOT NULL,
                payload LONGTEXT NOT NULL,
                attempts INT UNSIGNED NOT NULL DEFAULT 0,
                max_attempts INT UNSIGNED NOT NULL DEFAULT 1,
                available_at DATETIME NOT NULL,
                reserved_at DATETIME NULL,
                last_error TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_queue_available (queue_name, available_at),
                INDEX idx_queue_reserved (queue_name, reserved_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS {$failedTable} (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                queue_name VARCHAR(64) NOT NULL,
                job_class VARCHAR(191) NOT NULL,
                display_name VARCHAR(191) NOT NULL,
                payload LONGTEXT NOT NULL,
                attempts INT UNSIGNED NOT NULL DEFAULT 0,
                max_attempts INT UNSIGNED NOT NULL DEFAULT 1,
                error_message TEXT NOT NULL,
                failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                original_job_id BIGINT UNSIGNED NULL,
                INDEX idx_failed_queue (queue_name),
                INDEX idx_failed_at (failed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        self::$ready = true;
    }

    /**
     * Quotes a queue table identifier for schema statements.
     */
    private static function quote(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
