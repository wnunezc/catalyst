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
 * Generates a persisted report run through the queue.
 *
 * @package Catalyst\Framework\Reporting\Jobs
 * Responsibility: Carries a report-run identifier across the queue boundary and invokes report generation.
 */
final class RunReportJob implements QueueableJobInterface
{
    /**
     * Initializes the Run Report Job instance.
     *
     * Responsibility: Initializes the Run Report Job instance.
     */
    public function __construct(
        private readonly int $reportRunId,
        private readonly string $queueNameOverride = 'reports'
    ) {
    }

    /**
     * Processes the queued report run.
     *
     * Responsibility: Processes the queued report run.
     */
    public function handle(): void
    {
        ReportingManager::getInstance()->process($this->reportRunId);
    }

    /**
     * Returns the report-generation label.
     *
     * Responsibility: Returns the report-generation label.
     */
    public function displayName(): string
    {
        return 'reporting:run';
    }

    /**
     * Returns the queue selected for report generation.
     *
     * Responsibility: Returns the queue selected for report generation.
     */
    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    /**
     * Returns the allowed report-generation attempt count.
     *
     * Responsibility: Returns the allowed report-generation attempt count.
     */
    public function maxAttempts(): int
    {
        return 1;
    }

    /**
     * Returns the retry delay for report-generation failures.
     *
     * Responsibility: Returns the retry delay for report-generation failures.
     */
    public function backoffSeconds(): int
    {
        return 0;
    }

    /**
     * Exports the report-run identifier and queue routing for persistence.
     *
     * Responsibility: Exports the report-run identifier and queue routing for persistence.
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
     * Restores a report-generation job from persisted state.
     *
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
