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

namespace Catalyst\Framework\Retention\Jobs;

use Catalyst\Framework\Queue\QueueableJobInterface;
use Catalyst\Framework\Retention\RetentionManager;

/**
 * Executes retention policies asynchronously through the maintenance queue.
 *
 * @package Catalyst\Framework\Retention\Jobs
 * Responsibility: Carries retention scope across the queue boundary and invokes policy evaluation.
 */
final class RunRetentionPoliciesJob implements QueueableJobInterface
{
    /**
     * Initializes the Run Retention Policies Job instance.
     *
     * Responsibility: Initializes the Run Retention Policies Job instance.
     */
    public function __construct(
        private readonly ?string $resourceKey = null,
        private readonly int $limit = 250,
        private readonly string $queueNameOverride = 'maintenance'
    ) {
    }

    /**
     * Runs retention policies for the configured resource scope.
     *
     * Responsibility: Runs retention policies for the configured resource scope.
     */
    public function handle(): void
    {
        RetentionManager::getInstance()->run($this->resourceKey, false, $this->limit);
    }

    /**
     * Returns the retention-job label.
     *
     * Responsibility: Returns the retention-job label.
     */
    public function displayName(): string
    {
        return 'retention:run-policies';
    }

    /**
     * Returns the queue selected for retention work.
     *
     * Responsibility: Returns the queue selected for retention work.
     */
    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    /**
     * Returns the allowed retention-attempt count.
     *
     * Responsibility: Returns the allowed retention-attempt count.
     */
    public function maxAttempts(): int
    {
        return 1;
    }

    /**
     * Returns the retry delay for retention failures.
     *
     * Responsibility: Returns the retry delay for retention failures.
     */
    public function backoffSeconds(): int
    {
        return 0;
    }

    /**
     * Exports retention scope and queue routing for persistence.
     *
     * Responsibility: Exports retention scope and queue routing for persistence.
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'resource_key' => $this->resourceKey,
            'limit' => $this->limit,
            'queue_name' => $this->queueNameOverride,
        ];
    }

    /**
     * Restores a retention job from persisted state.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        return new self(
            resourceKey: ($payload['resource_key'] ?? null) !== null ? trim((string) $payload['resource_key']) ?: null : null,
            limit: max(1, (int) ($payload['limit'] ?? 250)),
            queueNameOverride: trim((string) ($payload['queue_name'] ?? 'maintenance')) ?: 'maintenance'
        );
    }
}
