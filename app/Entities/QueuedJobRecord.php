<?php

declare(strict_types=1);

namespace Catalyst\Entities;

final class QueuedJobRecord
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly int $id,
        public readonly string $queueName,
        public readonly string $jobClass,
        public readonly string $displayName,
        public readonly array $payload,
        public readonly int $attempts,
        public readonly int $maxAttempts,
        public readonly string $availableAt,
        public readonly ?string $reservedAt,
        public readonly ?string $lastError,
        public readonly string $createdAt
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $payload = [];

        if (isset($row['payload']) && is_string($row['payload']) && $row['payload'] !== '') {
            $decoded = json_decode($row['payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        return new self(
            id: (int) ($row['id'] ?? 0),
            queueName: (string) ($row['queue_name'] ?? 'default'),
            jobClass: (string) ($row['job_class'] ?? ''),
            displayName: (string) ($row['display_name'] ?? ($row['job_class'] ?? 'job')),
            payload: $payload,
            attempts: (int) ($row['attempts'] ?? 0),
            maxAttempts: (int) ($row['max_attempts'] ?? 1),
            availableAt: (string) ($row['available_at'] ?? ''),
            reservedAt: isset($row['reserved_at']) ? (string) $row['reserved_at'] : null,
            lastError: isset($row['last_error']) ? (string) $row['last_error'] : null,
            createdAt: (string) ($row['created_at'] ?? '')
        );
    }
}
