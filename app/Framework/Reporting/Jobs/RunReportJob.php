<?php

declare(strict_types=1);

namespace Catalyst\Framework\Reporting\Jobs;

use Catalyst\Framework\Queue\QueueableJobInterface;
use Catalyst\Framework\Reporting\ReportingManager;

final class RunReportJob implements QueueableJobInterface
{
    public function __construct(
        private readonly int $reportRunId,
        private readonly string $queueNameOverride = 'reports'
    ) {
    }

    public function handle(): void
    {
        ReportingManager::getInstance()->process($this->reportRunId);
    }

    public function displayName(): string
    {
        return 'reporting:run';
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
            'report_run_id' => $this->reportRunId,
            'queue_name' => $this->queueNameOverride,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        return new self(
            reportRunId: max(1, (int) ($payload['report_run_id'] ?? 0)),
            queueNameOverride: trim((string) ($payload['queue_name'] ?? 'reports')) ?: 'reports'
        );
    }
}
