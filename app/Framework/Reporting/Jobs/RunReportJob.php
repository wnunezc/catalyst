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

namespace Catalyst\Framework\Reporting\Jobs;

use Catalyst\Framework\Queue\QueueableJobInterface;
use Catalyst\Framework\Reporting\ReportingManager;

/**
 * Defines the Run Report Job class contract.
 *
 * @package Catalyst\Framework\Reporting\Jobs
 * Responsibility: Coordinates the run report job behavior within its module boundary.
 */
final class RunReportJob implements QueueableJobInterface
{
    /**
     * Initializes the Run Report Job instance.
     */
    public function __construct(
        private readonly int $reportRunId,
        private readonly string $queueNameOverride = 'reports'
    ) {
    }

    /**
     * Handles the request workflow.
     */
    public function handle(): void
    {
        ReportingManager::getInstance()->process($this->reportRunId);
    }

    /**
     * Handles the display name workflow.
     */
    public function displayName(): string
    {
        return 'reporting:run';
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
