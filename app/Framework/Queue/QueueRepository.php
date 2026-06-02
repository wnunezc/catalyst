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

use Catalyst\Entities\QueuedJobRecord;
use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Traits\SingletonTrait;
use DateTimeImmutable;
use RuntimeException;

/**
 * Persists queued jobs, failed jobs, and their processing state.
 *
 * @package Catalyst\Framework\Queue
 * Responsibility: Provides the database operations required to enqueue, reserve, retry, complete, inspect, and prune queued work.
 */
final class QueueRepository
{
    use SingletonTrait;

    /**
     * Inserts a job that is ready for queue processing at the requested time.
     *
     * Responsibility: Inserts a job that is ready for queue processing at the requested time.
     * @param array<string, mixed> $payload
     */
    public function enqueue(
        string $queueName,
        string $jobClass,
        string $displayName,
        array $payload,
        int $maxAttempts,
        DateTimeImmutable $availableAt
    ): int {
        QueueSchemaManager::ensure();

        $config = QueueSettings::current();
        $table = $this->quote((string) $config['jobs_table']);

        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $this->connection()->execute(
            "INSERT INTO {$table} (queue_name, job_class, display_name, payload, attempts, max_attempts, available_at, reserved_at, last_error)
             VALUES (:queue_name, :job_class, :display_name, :payload, 0, :max_attempts, :available_at, NULL, NULL)",
            [
                ':queue_name' => $queueName,
                ':job_class' => $jobClass,
                ':display_name' => $displayName,
                ':payload' => $jsonPayload,
                ':max_attempts' => max(1, $maxAttempts),
                ':available_at' => $availableAt->format('Y-m-d H:i:s'),
            ]
        );

        return (int) $this->connection()->getPdo()->lastInsertId();
    }

    /**
     * Atomically reserves the next available job from a queue.
     *
     * Responsibility: Atomically reserves the next available job from a queue.
     */
    public function reserveNext(?string $queueName = null): ?QueuedJobRecord
    {
        QueueSchemaManager::ensure();

        $config = QueueSettings::current();
        $table = $this->quote((string) $config['jobs_table']);
        $queue = $queueName !== null && trim($queueName) !== ''
            ? trim($queueName)
            : (string) $config['default_queue'];

        $now = gmdate('Y-m-d H:i:s');
        $staleBefore = gmdate('Y-m-d H:i:s', time() - (int) $config['stale_after_seconds']);

        return $this->connection()->transaction(function (Connection $connection) use ($table, $queue, $now, $staleBefore): ?QueuedJobRecord {
            $row = $connection->selectOne(
                "SELECT *
                 FROM {$table}
                 WHERE queue_name = :queue_name
                   AND available_at <= :available_at
                   AND (reserved_at IS NULL OR reserved_at <= :stale_before)
                 ORDER BY available_at ASC, id ASC
                 LIMIT 1
                 FOR UPDATE",
                [
                    ':queue_name' => $queue,
                    ':available_at' => $now,
                    ':stale_before' => $staleBefore,
                ]
            );

            if ($row === null) {
                return null;
            }

            $connection->execute(
                "UPDATE {$table}
                 SET reserved_at = :reserved_at,
                     attempts = attempts + 1
                 WHERE id = :id",
                [
                    ':reserved_at' => $now,
                    ':id' => (int) $row['id'],
                ]
            );

            $row['attempts'] = ((int) ($row['attempts'] ?? 0)) + 1;
            $row['reserved_at'] = $now;

            return QueuedJobRecord::fromRow($row);
        });
    }

    /**
     * Removes a successfully processed job from the pending queue.
     *
     * Responsibility: Removes a successfully processed job from the pending queue.
     */
    public function complete(int $jobId): void
    {
        QueueSchemaManager::ensure();

        $table = $this->quote((string) QueueSettings::current()['jobs_table']);
        $this->connection()->execute(
            "DELETE FROM {$table} WHERE id = :id",
            [':id' => $jobId]
        );
    }

    /**
     * Releases a failed attempt back to the queue with a retry delay.
     *
     * Responsibility: Releases a failed attempt back to the queue with a retry delay.
     */
    public function releaseForRetry(QueuedJobRecord $job, string $error, int $delaySeconds): void
    {
        QueueSchemaManager::ensure();

        $table = $this->quote((string) QueueSettings::current()['jobs_table']);
        $availableAt = gmdate('Y-m-d H:i:s', time() + max(0, $delaySeconds));

        $this->connection()->execute(
            "UPDATE {$table}
             SET reserved_at = NULL,
                 available_at = :available_at,
                 last_error = :last_error
             WHERE id = :id",
            [
                ':available_at' => $availableAt,
                ':last_error' => mb_substr($error, 0, 65535),
                ':id' => $job->id,
            ]
        );
    }

    /**
     * Moves an exhausted job from the pending queue into failed history.
     *
     * Responsibility: Moves an exhausted job from the pending queue into failed history.
     */
    public function moveToFailed(QueuedJobRecord $job, string $error): int
    {
        QueueSchemaManager::ensure();

        $config = QueueSettings::current();
        $failedTable = $this->quote((string) $config['failed_jobs_table']);
        $jobsTable = $this->quote((string) $config['jobs_table']);

        $this->connection()->execute(
            "INSERT INTO {$failedTable} (queue_name, job_class, display_name, payload, attempts, max_attempts, error_message, original_job_id)
             VALUES (:queue_name, :job_class, :display_name, :payload, :attempts, :max_attempts, :error_message, :original_job_id)",
            [
                ':queue_name' => $job->queueName,
                ':job_class' => $job->jobClass,
                ':display_name' => $job->displayName,
                ':payload' => json_encode($job->payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                ':attempts' => $job->attempts,
                ':max_attempts' => $job->maxAttempts,
                ':error_message' => mb_substr($error, 0, 65535),
                ':original_job_id' => $job->id,
            ]
        );

        $failedId = (int) $this->connection()->getPdo()->lastInsertId();

        $this->connection()->execute(
            "DELETE FROM {$jobsTable} WHERE id = :id",
            [':id' => $job->id]
        );

        return $failedId;
    }

    /**
     * Lists failed jobs, optionally restricted to one queue.
     *
     * Responsibility: Lists failed jobs, optionally restricted to one queue.
     * @return array<int, array<string, mixed>>
     */
    public function listFailed(int $limit = 50, ?string $queueName = null): array
    {
        QueueSchemaManager::ensure();

        $config = QueueSettings::current();
        $failedTable = $this->quote((string) $config['failed_jobs_table']);
        $params = [
            ':limit' => max(1, $limit),
        ];

        $where = '';
        if ($queueName !== null && trim($queueName) !== '') {
            $where = 'WHERE queue_name = :queue_name';
            $params[':queue_name'] = trim($queueName);
        }

        return $this->connection()->select(
            "SELECT id, queue_name, display_name, job_class, attempts, max_attempts, failed_at, error_message, original_job_id
             FROM {$failedTable}
             {$where}
             ORDER BY failed_at DESC, id DESC
             LIMIT :limit",
            $params
        );
    }

    /**
     * Requeues one or all failed jobs and returns their new identifiers.
     *
     * Responsibility: Requeues one or all failed jobs and returns their new identifiers.
     * @return int[]
     */
    public function retryFailed(?int $failedJobId = null): array
    {
        QueueSchemaManager::ensure();

        $config = QueueSettings::current();
        $failedTable = $this->quote((string) $config['failed_jobs_table']);
        $jobsTable = $this->quote((string) $config['jobs_table']);

        $params = [];
        $where = '';

        if ($failedJobId !== null) {
            $where = 'WHERE id = :id';
            $params[':id'] = $failedJobId;
        }

        $rows = $this->connection()->select(
            "SELECT * FROM {$failedTable} {$where} ORDER BY id ASC",
            $params
        );

        if ($rows === []) {
            return [];
        }

        $requeued = [];

        $this->connection()->transaction(function (Connection $connection) use ($rows, $jobsTable, $failedTable, &$requeued): void {
            foreach ($rows as $row) {
                $connection->execute(
                    "INSERT INTO {$jobsTable} (queue_name, job_class, display_name, payload, attempts, max_attempts, available_at, reserved_at, last_error)
                     VALUES (:queue_name, :job_class, :display_name, :payload, 0, :max_attempts, :available_at, NULL, NULL)",
                    [
                        ':queue_name' => (string) $row['queue_name'],
                        ':job_class' => (string) $row['job_class'],
                        ':display_name' => (string) $row['display_name'],
                        ':payload' => (string) $row['payload'],
                        ':max_attempts' => (int) $row['max_attempts'],
                        ':available_at' => gmdate('Y-m-d H:i:s'),
                    ]
                );

                $requeued[] = (int) $connection->getPdo()->lastInsertId();

                $connection->execute(
                    "DELETE FROM {$failedTable} WHERE id = :id",
                    [':id' => (int) $row['id']]
                );
            }
        });

        return $requeued;
    }

    /**
     * Counts pending jobs, optionally restricted to one queue.
     *
     * Responsibility: Counts pending jobs, optionally restricted to one queue.
     */
    public function pendingCount(?string $queueName = null): int
    {
        QueueSchemaManager::ensure();

        $table = $this->quote((string) QueueSettings::current()['jobs_table']);
        $params = [];
        $where = '';

        if ($queueName !== null && trim($queueName) !== '') {
            $where = 'WHERE queue_name = :queue_name';
            $params[':queue_name'] = trim($queueName);
        }

        $row = $this->connection()->selectOne(
            "SELECT COUNT(*) AS total FROM {$table} {$where}",
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Counts failed jobs, optionally restricted to one queue.
     *
     * Responsibility: Counts failed jobs, optionally restricted to one queue.
     */
    public function failedCount(?string $queueName = null): int
    {
        QueueSchemaManager::ensure();

        $table = $this->quote((string) QueueSettings::current()['failed_jobs_table']);
        $params = [];
        $where = '';

        if ($queueName !== null && trim($queueName) !== '') {
            $where = 'WHERE queue_name = :queue_name';
            $params[':queue_name'] = trim($queueName);
        }

        $row = $this->connection()->selectOne(
            "SELECT COUNT(*) AS total FROM {$table} {$where}",
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Deletes failed-job history older than the configured window.
     *
     * Responsibility: Deletes failed-job history older than the configured window.
     */
    public function pruneFailedJobs(int $olderThanDays = 14): int
    {
        QueueSchemaManager::ensure();

        $table = $this->quote((string) QueueSettings::current()['failed_jobs_table']);
        $cutoff = gmdate('Y-m-d H:i:s', time() - (max(1, $olderThanDays) * 86400));

        return $this->connection()->execute(
            "DELETE FROM {$table} WHERE failed_at < :cutoff",
            [':cutoff' => $cutoff]
        );
    }

    /**
     * Returns an operational snapshot of the queue backend.
     *
     * Responsibility: Returns an operational snapshot of the queue backend.
     * @return array{connection:string,default_queue:string,pending_jobs:int,failed_jobs:int}
     */
    public function summary(): array
    {
        $config = QueueSettings::current();
        QueueSchemaManager::ensure();

        return [
            'connection' => (string) $config['connection'],
            'default_queue' => (string) $config['default_queue'],
            'pending_jobs' => $this->pendingCount(),
            'failed_jobs' => $this->failedCount(),
        ];
    }

    /**
     * Resolves the configured database connection for queue storage.
     *
     * Responsibility: Resolves the configured database connection for queue storage.
     */
    private function connection(): Connection
    {
        $config = QueueSettings::current();

        return DatabaseManager::getInstance()->connection((string) $config['connection']);
    }

    /**
     * Quotes a validated queue table identifier.
     *
     * Responsibility: Quotes a validated queue table identifier.
     */
    private function quote(string $identifier): string
    {
        if ($identifier === '' || preg_match('/^[A-Za-z0-9_]+$/', $identifier) !== 1) {
            throw new RuntimeException('Unsafe queue table identifier: ' . $identifier);
        }

        return '`' . $identifier . '`';
    }
}
