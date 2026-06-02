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
 * Defines the Prune Queue History Job class contract.
 *
 * @package Catalyst\Framework\Queue\Jobs
 * Responsibility: Coordinates the prune queue history job behavior within its module boundary.
 */
final class PruneQueueHistoryJob implements QueueableJobInterface
{
    /**
     * Initializes the Prune Queue History Job instance.
     */
    public function __construct(
        private readonly int $failedOlderThanDays = 14,
        private readonly int $schedulerOlderThanDays = 30,
        private readonly string $queueNameOverride = 'maintenance'
    ) {
    }

    /**
     * Handles the request workflow.
     */
    public function handle(): void
    {
        QueueRepository::getInstance()->pruneFailedJobs($this->failedOlderThanDays);
        ScheduleRepository::getInstance()->pruneRuns($this->schedulerOlderThanDays);
    }

    /**
     * Handles the display name workflow.
     */
    public function displayName(): string
    {
        return 'maintenance:prune-queue-history';
    }

    /**
     * Handles the queue name workflow.
     */
    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    /**
     * Handles the max attempts workflow.
     */
    public function maxAttempts(): int
    {
        return 1;
    }

    /**
     * Handles the backoff seconds workflow.
     */
    public function backoffSeconds(): int
    {
        return 0;
    }

    /**
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
