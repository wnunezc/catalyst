<?php

declare(strict_types=1);

namespace Catalyst\Framework\Queue\Jobs;

use Catalyst\Framework\Queue\QueueRepository;
use Catalyst\Framework\Queue\QueueableJobInterface;
use Catalyst\Framework\Schedule\ScheduleRepository;

final class PruneQueueHistoryJob implements QueueableJobInterface
{
    public function __construct(
        private readonly int $failedOlderThanDays = 14,
        private readonly int $schedulerOlderThanDays = 30,
        private readonly string $queueNameOverride = 'maintenance'
    ) {
    }

    public function handle(): void
    {
        QueueRepository::getInstance()->pruneFailedJobs($this->failedOlderThanDays);
        ScheduleRepository::getInstance()->pruneRuns($this->schedulerOlderThanDays);
    }

    public function displayName(): string
    {
        return 'maintenance:prune-queue-history';
    }

    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    public function maxAttempts(): int
    {
        return 1;
    }

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
