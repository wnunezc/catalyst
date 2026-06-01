<?php

declare(strict_types=1);

namespace Catalyst\Framework\Schedule;

use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Queue\QueueSettings;
use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;

final class ScheduleRepository
{
    use SingletonTrait;

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

    private function connection(): Connection
    {
        return DatabaseManager::getInstance()->connection((string) QueueSettings::current()['connection']);
    }

    private function quote(string $identifier): string
    {
        if ($identifier === '' || preg_match('/^[A-Za-z0-9_]+$/', $identifier) !== 1) {
            throw new RuntimeException('Unsafe scheduler table identifier: ' . $identifier);
        }

        return '`' . $identifier . '`';
    }
}
