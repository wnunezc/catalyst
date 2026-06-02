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

namespace Catalyst\Framework\Queue\Jobs;

use Catalyst\Framework\Queue\QueueRepository;
use Catalyst\Framework\Queue\QueueableJobInterface;
use Catalyst\Framework\Schedule\ScheduleRepository;

/**
 * Prunes failed queue jobs and old scheduler history from the maintenance queue.
 *
 * @package Catalyst\Framework\Queue\Jobs
 * Responsibility: Executes periodic cleanup for queue failures and scheduler run records.
 */
final class PruneQueueHistoryJob implements QueueableJobInterface
{
    /**
     * Initializes the Prune Queue History Job instance.
     *
     * Responsibility: Initializes the Prune Queue History Job instance.
     */
    public function __construct(
        private readonly int $failedOlderThanDays = 14,
        private readonly int $schedulerOlderThanDays = 30,
        private readonly string $queueNameOverride = 'maintenance'
    ) {
    }

    /**
     * Deletes queue and scheduler history outside their retention windows.
     *
     * Responsibility: Deletes queue and scheduler history outside their retention windows.
     */
    public function handle(): void
    {
        QueueRepository::getInstance()->pruneFailedJobs($this->failedOlderThanDays);
        ScheduleRepository::getInstance()->pruneRuns($this->schedulerOlderThanDays);
    }

    /**
     * Returns the maintenance-job label.
     *
     * Responsibility: Returns the maintenance-job label.
     */
    public function displayName(): string
    {
        return 'maintenance:prune-queue-history';
    }

    /**
     * Returns the queue selected for maintenance work.
     *
     * Responsibility: Returns the queue selected for maintenance work.
     */
    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    /**
     * Returns the allowed maintenance-attempt count.
     *
     * Responsibility: Returns the allowed maintenance-attempt count.
     */
    public function maxAttempts(): int
    {
        return 1;
    }

    /**
     * Returns the retry delay for maintenance failures.
     *
     * Responsibility: Returns the retry delay for maintenance failures.
     */
    public function backoffSeconds(): int
    {
        return 0;
    }

    /**
     * Exports cleanup windows and queue routing for persistence.
     *
     * Responsibility: Exports cleanup windows and queue routing for persistence.
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'failed_older_than_days' => $this->failedOlderThanDays,
            'scheduler_older_than_days' => $this->schedulerOlderThanDays,
            'queue_name' => $this->queueNameOverride,
        ];
    }

    /**
     * Restores a cleanup job from persisted state.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        return new self(
            failedOlderThanDays: max(1, (int) ($payload['failed_older_than_days'] ?? 14)),
            schedulerOlderThanDays: max(1, (int) ($payload['scheduler_older_than_days'] ?? 30)),
            queueNameOverride: trim((string) ($payload['queue_name'] ?? 'maintenance')) ?: 'maintenance'
        );
    }
}
