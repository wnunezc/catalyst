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

namespace Catalyst\Framework\Presence;

use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\WebSocket\WebSocketPublisher;

/**
 * Coordinates collaborative record presence.
 *
 * @package Catalyst\Framework\Presence
 * Responsibility: Converts record claims into presence payloads and broadcasts claim snapshots through WebSocket.
 */
final class PresenceManager
{
    use SingletonTrait;

    /**
     * Converts a record claim into a client-facing presence payload.
     *
     * Responsibility: Converts a record claim into a client-facing presence payload.
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
     * Publishes a valid record claim snapshot to subscribed clients.
     *
     * Responsibility: Publishes a valid record claim snapshot to subscribed clients.
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
     * Refreshes a record claim and returns its updated presence payload.
     *
     * Responsibility: Refreshes a record claim and returns its updated presence payload.
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
