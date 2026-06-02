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

use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Queue\QueueSettings;
use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;

/**
 * Persists scheduler slot claims and execution history.
 *
 * @package Catalyst\Framework\Schedule
 * Responsibility: Prevents duplicate slot dispatches, records queued or skipped runs, summarizes history, and prunes old records.
 */
final class ScheduleRepository
{
    use SingletonTrait;

    /**
     * Claims a scheduler slot by inserting its unique task-and-slot record.
     *
     * Responsibility: Claims a scheduler slot by inserting its unique task-and-slot record.
     */
    public function claimSlot(string $taskName, string $expression, string $slotKey, string $queueName): bool
    {
        ScheduleSchemaManager::ensure();
        $table = $this->quote((string) ScheduleSettings::current()['history_table']);

        try {
            $this->connection()->execute(
                "INSERT INTO {$table} (task_name, expression, slot_key, queue_name, status)
                 VALUES (:task_name, :expression, :slot_key, :queue_name, :status)",
                [
                    ':task_name' => $taskName,
                    ':expression' => $expression,
                    ':slot_key' => $slotKey,
                    ':queue_name' => $queueName,
                    ':status' => 'claimed',
                ]
            );

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Marks a claimed slot as queued with its generated job identifier.
     *
     * Responsibility: Marks a claimed slot as queued with its generated job identifier.
     */
    public function markQueued(string $taskName, string $slotKey, int $jobId, ?string $message = null): void
    {
        ScheduleSchemaManager::ensure();
        $table = $this->quote((string) ScheduleSettings::current()['history_table']);

        $this->connection()->execute(
            "UPDATE {$table}
             SET status = :status,
                 queued_job_id = :queued_job_id,
                 message = :message,
                 finished_at = :finished_at
             WHERE task_name = :task_name
               AND slot_key = :slot_key",
            [
                ':status' => 'queued',
                ':queued_job_id' => $jobId,
                ':message' => $message,
                ':finished_at' => gmdate('Y-m-d H:i:s'),
                ':task_name' => $taskName,
                ':slot_key' => $slotKey,
            ]
        );
    }

    /**
     * Records a scheduler slot that was skipped before queue dispatch.
     *
     * Responsibility: Records a scheduler slot that was skipped before queue dispatch.
     */
    public function markSkipped(string $taskName, string $expression, string $slotKey, string $queueName, string $message): void
    {
        ScheduleSchemaManager::ensure();
        $table = $this->quote((string) ScheduleSettings::current()['history_table']);

        $this->connection()->execute(
            "INSERT INTO {$table} (task_name, expression, slot_key, queue_name, status, message, finished_at)
             VALUES (:task_name, :expression, :slot_key, :queue_name, :status, :message, :finished_at)",
            [
                ':task_name' => $taskName,
                ':expression' => $expression,
                ':slot_key' => $slotKey,
                ':queue_name' => $queueName,
                ':status' => 'skipped',
                ':message' => mb_substr($message, 0, 255),
                ':finished_at' => gmdate('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Deletes scheduler history older than the requested retention window.
     *
     * Responsibility: Deletes scheduler history older than the requested retention window.
     */
    public function pruneRuns(int $olderThanDays = 30): int
    {
        ScheduleSchemaManager::ensure();
        $table = $this->quote((string) ScheduleSettings::current()['history_table']);
        $cutoff = gmdate('Y-m-d H:i:s', time() - (max(1, $olderThanDays) * 86400));

        return $this->connection()->execute(
            "DELETE FROM {$table} WHERE created_at < :cutoff",
            [':cutoff' => $cutoff]
        );
    }

    /**
     * Returns an operational summary of scheduler history.
     *
     * Responsibility: Returns an operational summary of scheduler history.
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        ScheduleSchemaManager::ensure();
        $table = $this->quote((string) ScheduleSettings::current()['history_table']);
        $row = $this->connection()->selectOne(
            "SELECT COUNT(*) AS total,
                    MAX(created_at) AS last_run
             FROM {$table}"
        );

        return [
            'history_table' => (string) ScheduleSettings::current()['history_table'],
            'total_runs' => (int) ($row['total'] ?? 0),
            'last_run' => isset($row['last_run']) ? (string) $row['last_run'] : null,
        ];
    }

    /**
     * Resolves the queue database connection used for scheduler history.
     *
     * Responsibility: Resolves the queue database connection used for scheduler history.
     */
    private function connection(): Connection
    {
        return DatabaseManager::getInstance()->connection((string) QueueSettings::current()['connection']);
    }

    /**
     * Quotes a validated scheduler table identifier.
     *
     * Responsibility: Quotes a validated scheduler table identifier.
     */
    private function quote(string $identifier): string
    {
        if ($identifier === '' || preg_match('/^[A-Za-z0-9_]+$/', $identifier) !== 1) {
            throw new RuntimeException('Unsafe scheduler table identifier: ' . $identifier);
        }

        return '`' . $identifier . '`';
    }
}
