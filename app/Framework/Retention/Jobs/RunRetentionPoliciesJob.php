<?php

declare(strict_types=1);

namespace Catalyst\Framework\Retention\Jobs;

use Catalyst\Framework\Queue\QueueableJobInterface;
use Catalyst\Framework\Retention\RetentionManager;

final class RunRetentionPoliciesJob implements QueueableJobInterface
{
    public function __construct(
        private readonly ?string $resourceKey = null,
        private readonly int $limit = 250,
        private readonly string $queueNameOverride = 'maintenance'
    ) {
    }

    public function handle(): void
    {
        RetentionManager::getInstance()->run($this->resourceKey, false, $this->limit);
    }

    public function displayName(): string
    {
        return 'retention:run-policies';
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
