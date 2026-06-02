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
 * Defines the Run Retention Policies Job class contract.
 *
 * @package Catalyst\Framework\Retention\Jobs
 * Responsibility: Coordinates the run retention policies job behavior within its module boundary.
 */
final class RunRetentionPoliciesJob implements QueueableJobInterface
{
    /**
     * Initializes the Run Retention Policies Job instance.
     */
    public function __construct(
        private readonly ?string $resourceKey = null,
        private readonly int $limit = 250,
        private readonly string $queueNameOverride = 'maintenance'
    ) {
    }

    /**
     * Handles the request workflow.
     */
    public function handle(): void
    {
        RetentionManager::getInstance()->run($this->resourceKey, false, $this->limit);
    }

    /**
     * Handles the display name workflow.
     */
    public function displayName(): string
    {
        return 'retention:run-policies';
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
            'resource_key' => $this->resourceKey,
            'limit' => $this->limit,
            'queue_name' => $this->queueNameOverride,
        ];
    }

    /**
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
