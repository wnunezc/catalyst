<?php

declare(strict_types=1);

namespace Catalyst\Framework\Presence;

use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\WebSocket\WebSocketPublisher;

final class PresenceManager
{
    use SingletonTrait;

    /**
     * @param array<string, mixed>|null $claim
     * @return array<string, mixed>|null
     */
    public function presencePayload(?array $claim): ?array
    {
        if ($claim === null) {
            return null;
        }

        return [
            'tenant_id' => (int) ($claim['tenant_id'] ?? 0),
            'tenant_key' => (string) ($claim['tenant_key'] ?? ''),
            'resource_key' => (string) ($claim['resource_key'] ?? ''),
            'record_id' => (int) ($claim['record_id'] ?? 0),
            'status' => (string) ($claim['status'] ?? 'released'),
            'active' => (bool) ($claim['active'] ?? false),
            'claimed_by' => ($claim['claimed_by'] ?? null) !== null ? (int) $claim['claimed_by'] : null,
            'claimed_by_label' => (string) ($claim['claimed_by_label'] ?? ''),
            'claimed_at' => (string) ($claim['claimed_at'] ?? ''),
            'expires_at' => (string) ($claim['expires_at'] ?? ''),
            'released_at' => (string) ($claim['released_at'] ?? ''),
            'seconds_to_expiry' => ($claim['seconds_to_expiry'] ?? null) !== null ? (int) $claim['seconds_to_expiry'] : null,
        ];
    }

    /**
     * @param array<string, mixed>|null $claim
     */
    public function publishClaimSnapshot(?array $claim): bool
    {
        $payload = $this->presencePayload($claim);

        if ($payload === null) {
            return false;
        }

        $tenantId = (int) ($payload['tenant_id'] ?? 0);
        $resourceKey = trim((string) ($payload['resource_key'] ?? ''));
        $recordId = (int) ($payload['record_id'] ?? 0);

        if ($tenantId <= 0 || $resourceKey === '' || $recordId <= 0) {
            return false;
        }

        return WebSocketPublisher::getInstance()->publishToResource(
            tenantId: $tenantId,
            resourceKey: $resourceKey,
            recordId: $recordId,
            payload: $payload
        );
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    public function heartbeat(
        string $resourceKey,
        int $recordId,
        ?int $actorId = null,
        ?string $actorLabel = null,
        int $ttlSeconds = 120,
        array $metadata = []
    ): array {
        $claim = RecordClaimManager::getInstance()->acquire(
            resourceKey: $resourceKey,
            recordId: $recordId,
            actorId: $actorId,
            actorLabel: $actorLabel,
            ttlSeconds: $ttlSeconds,
            metadata: array_merge($metadata, [
                'source' => 'presence-heartbeat',
            ])
        );

        return $this->presencePayload($claim) ?? [];
    }
}
