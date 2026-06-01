<?php

declare(strict_types=1);

namespace Catalyst\Framework\Concurrency;

use Catalyst\Entities\RecordClaim;
use Catalyst\Framework\Audit\AuditLogManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Presence\PresenceManager;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Traits\SingletonTrait;
use DateTimeImmutable;
use RuntimeException;

final class RecordClaimManager
{
    use SingletonTrait;

    private RecordClaimRepository $repository;
    private DatabaseManager $db;

    protected function __construct()
    {
        $this->repository = RecordClaimRepository::getInstance();
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    public function acquire(
        string $resourceKey,
        int $recordId,
        ?int $actorId = null,
        ?string $actorLabel = null,
        int $ttlSeconds = 900,
        array $metadata = []
    ): array {
        $resourceKey = trim($resourceKey);
        $recordId = max(0, $recordId);

        if ($resourceKey === '' || $recordId <= 0) {
            throw new RuntimeException('resource_key and record_id are required to acquire a claim.');
        }

        $ttlSeconds = max(30, $ttlSeconds);
        [$actorId, $actorLabel] = $this->resolveActor($actorId, $actorLabel);
        $claimedAt = $this->now();
        $expiresAt = $claimedAt->modify('+' . $ttlSeconds . ' seconds');
        $claimToken = bin2hex(random_bytes(16));

        $snapshot = $this->db->connection()->transaction(function () use (
            $resourceKey,
            $recordId,
            $actorId,
            $actorLabel,
            $metadata,
            $claimedAt,
            $expiresAt,
            $claimToken
        ): array {
            $claim = $this->repository->lockByResource($resourceKey, $recordId);

            if (!$claim instanceof RecordClaim) {
                $claim = $this->createOrRecoverClaim(
                    $resourceKey,
                    $recordId,
                    $actorId,
                    $actorLabel,
                    $metadata,
                    $claimedAt,
                    $expiresAt,
                    $claimToken
                );

                return $this->normalizeClaim($claim);
            }

            $status = $this->repository->decorateRow($claim->toArray())['status'] ?? 'active';
            $before = $claim->toArray();

            if ($status === 'active' && !$this->isOwnedBy($claim, $actorId, $actorLabel)) {
                $this->audit(
                    'claim-conflict',
                    $resourceKey,
                    $recordId,
                    $before,
                    null,
                    [
                        'actor_id' => $actorId,
                        'actor_label' => $actorLabel,
                        'claim_id' => (int) $claim->getKey(),
                    ]
                );

                throw new RuntimeException(sprintf(
                    'Resource %s#%d is currently claimed by %s until %s.',
                    $resourceKey,
                    $recordId,
                    (string) ($before['claimed_by_label'] ?? ('user#' . ($before['claimed_by'] ?? 'unknown'))),
                    (string) ($before['expires_at'] ?? 'unknown')
                ));
            }

            $claim->fill([
                'claim_token' => $claimToken,
                'claimed_by' => $actorId,
                'claimed_by_label' => $actorLabel,
                'claimed_at' => $claimedAt->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'released_at' => null,
                'release_reason' => null,
                'metadata' => $metadata,
                'updated_by' => $actorId,
            ]);
            $claim->save();

            $action = match ($status) {
                'expired' => 'claim-reclaimed',
                'released' => 'claim-claimed',
                default => 'claim-renewed',
            };

            $after = $claim->toArray();
            $this->audit($action, $resourceKey, $recordId, $before, $after, [
                'actor_id' => $actorId,
                'actor_label' => $actorLabel,
                'claim_id' => (int) $claim->getKey(),
                'ttl_seconds' => $this->secondsBetween($claimedAt, $expiresAt),
            ]);

            return $this->normalizeClaim($claim);
        });

        PresenceManager::getInstance()->publishClaimSnapshot($snapshot);

        return $snapshot;
    }

    public function release(
        string $resourceKey,
        int $recordId,
        ?int $actorId = null,
        ?string $reason = null,
        ?string $claimToken = null,
        bool $force = false
    ): bool {
        $resourceKey = trim($resourceKey);
        $recordId = max(0, $recordId);

        if ($resourceKey === '' || $recordId <= 0) {
            throw new RuntimeException('resource_key and record_id are required to release a claim.');
        }

        [$actorId, $actorLabel] = $this->resolveActor($actorId, null);

        $result = $this->db->connection()->transaction(function () use (
            $resourceKey,
            $recordId,
            $actorId,
            $actorLabel,
            $reason,
            $claimToken,
            $force
        ): array {
            $claim = $this->repository->lockByResource($resourceKey, $recordId);

            if (!$claim instanceof RecordClaim) {
                return ['released' => false, 'snapshot' => null];
            }

            $snapshot = $this->repository->decorateRow($claim->toArray());
            if (($snapshot['status'] ?? 'released') === 'released') {
                return ['released' => false, 'snapshot' => $snapshot];
            }

            if (
                !$force
                && ($snapshot['status'] ?? 'active') === 'active'
                && !$this->isOwnedBy($claim, $actorId, $actorLabel)
            ) {
                throw new RuntimeException(sprintf(
                    'Resource %s#%d is currently claimed by another actor and cannot be released without --force.',
                    $resourceKey,
                    $recordId
                ));
            }

            if (
                !$force
                && $claimToken !== null
                && $claimToken !== ''
                && (string) ($snapshot['claim_token'] ?? '') !== $claimToken
            ) {
                throw new RuntimeException('The submitted claim token no longer matches the active claim.');
            }

            $before = $claim->toArray();
            $claim->fill([
                'released_at' => $this->now()->format('Y-m-d H:i:s'),
                'release_reason' => trim((string) $reason) !== '' ? trim((string) $reason) : 'manual release',
                'updated_by' => $actorId,
            ]);
            $claim->save();

            $this->audit(
                'claim-released',
                $resourceKey,
                $recordId,
                $before,
                $claim->toArray(),
                [
                    'actor_id' => $actorId,
                    'actor_label' => $actorLabel,
                    'force' => $force,
                    'claim_id' => (int) $claim->getKey(),
                ]
            );

            return [
                'released' => true,
                'snapshot' => $this->normalizeClaim($claim),
            ];
        });

        PresenceManager::getInstance()->publishClaimSnapshot(
            is_array($result['snapshot'] ?? null) ? $result['snapshot'] : null
        );

        return (bool) ($result['released'] ?? false);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function snapshot(string $resourceKey, int $recordId): ?array
    {
        $claim = $this->repository->findByResource(trim($resourceKey), $recordId);

        if (!$claim instanceof RecordClaim) {
            return null;
        }

        return $this->normalizeClaim($claim);
    }

    /**
     * @return array{actor_id:?int,actor_label:string}
     */
    public function actor(?int $actorId = null, ?string $actorLabel = null): array
    {
        [$resolvedId, $resolvedLabel] = $this->resolveActor($actorId, $actorLabel);

        return [
            'actor_id' => $resolvedId,
            'actor_label' => $resolvedLabel,
        ];
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    public function owns(array $snapshot, ?int $actorId = null, ?string $actorLabel = null): bool
    {
        $actor = $this->actor($actorId, $actorLabel);
        $claimedBy = $snapshot['claimed_by'] ?? null;
        $claimedByLabel = trim((string) ($snapshot['claimed_by_label'] ?? ''));

        if ($actor['actor_id'] !== null && $claimedBy !== null) {
            return (int) $claimedBy === (int) $actor['actor_id'];
        }

        return $claimedByLabel !== '' && $claimedByLabel === (string) $actor['actor_label'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function assertAvailable(
        string $resourceKey,
        int $recordId,
        ?int $actorId = null,
        ?string $actorLabel = null,
        ?string $claimToken = null
    ): ?array {
        $snapshot = $this->snapshot($resourceKey, $recordId);

        if ($snapshot === null) {
            return null;
        }

        if (($snapshot['status'] ?? 'released') !== 'active') {
            return $snapshot;
        }

        if (!$this->owns($snapshot, $actorId, $actorLabel)) {
            throw new RuntimeException(sprintf(
                'Resource %s#%d is currently claimed by %s until %s.',
                $resourceKey,
                $recordId,
                (string) ($snapshot['claimed_by_label'] ?? ('user#' . ($snapshot['claimed_by'] ?? 'unknown'))),
                (string) ($snapshot['expires_at'] ?? 'unknown')
            ));
        }

        $claimToken = trim((string) ($claimToken ?? ''));
        if ($claimToken !== '' && $claimToken !== (string) ($snapshot['claim_token'] ?? '')) {
            throw new RuntimeException('The submitted claim token no longer matches the active claim.');
        }

        return $snapshot;
    }

    /**
     * @return array{0:?int,1:string}
     */
    private function resolveActor(?int $actorId, ?string $actorLabel): array
    {
        $actor = null;

        if (SessionManager::getInstance()->isInitialized()) {
            try {
                $actor = AuthManager::getInstance()->user();
            } catch (\Throwable) {
                $actor = null;
            }
        }

        $resolvedId = $actorId ?? (($actor['id'] ?? null) !== null ? (int) $actor['id'] : null);
        $resolvedLabel = trim((string) ($actorLabel ?? ''));

        if ($resolvedLabel === '') {
            $resolvedLabel = trim((string) ($actor['name'] ?? $actor['email'] ?? ''));
        }

        if ($resolvedLabel === '' && $resolvedId !== null) {
            $resolvedLabel = 'user#' . $resolvedId;
        }

        if ($resolvedLabel === '') {
            $resolvedLabel = PHP_SAPI === 'cli' ? 'system-cli' : 'guest';
        }

        return [$resolvedId, $resolvedLabel];
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function createOrRecoverClaim(
        string $resourceKey,
        int $recordId,
        ?int $actorId,
        string $actorLabel,
        array $metadata,
        DateTimeImmutable $claimedAt,
        DateTimeImmutable $expiresAt,
        string $claimToken
    ): RecordClaim {
        try {
            $claim = new RecordClaim([
                'resource_key' => $resourceKey,
                'record_id' => $recordId,
                'claim_token' => $claimToken,
                'claimed_by' => $actorId,
                'claimed_by_label' => $actorLabel,
                'claimed_at' => $claimedAt->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'released_at' => null,
                'release_reason' => null,
                'metadata' => $metadata,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
            $claim->save();

            $this->audit(
                'claim-claimed',
                $resourceKey,
                $recordId,
                null,
                $claim->toArray(),
                [
                    'actor_id' => $actorId,
                    'actor_label' => $actorLabel,
                    'claim_id' => (int) $claim->getKey(),
                    'ttl_seconds' => $this->secondsBetween($claimedAt, $expiresAt),
                ]
            );

            return $claim;
        } catch (\Throwable) {
            $existing = $this->repository->lockByResource($resourceKey, $recordId);

            if ($existing instanceof RecordClaim) {
                return $existing;
            }

            throw new RuntimeException(sprintf(
                'Unable to create or resolve a claim row for %s#%d.',
                $resourceKey,
                $recordId
            ));
        }
    }

    private function isOwnedBy(RecordClaim $claim, ?int $actorId, string $actorLabel): bool
    {
        $claimedBy = $claim->getAttribute('claimed_by');
        $claimedByLabel = trim((string) ($claim->getAttribute('claimed_by_label') ?? ''));

        if ($actorId !== null && $claimedBy !== null) {
            return (int) $claimedBy === $actorId;
        }

        return $claimedByLabel !== '' && $claimedByLabel === $actorLabel;
    }

    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     * @param array<string, mixed> $metadata
     */
    private function audit(
        string $action,
        string $resourceKey,
        int $recordId,
        ?array $before,
        ?array $after,
        array $metadata
    ): void {
        AuditLogManager::getInstance()->recordOperation(
            channel: 'concurrency',
            action: $action,
            resource: $resourceKey,
            resourceId: $recordId,
            resourceLabel: $resourceKey . '#' . $recordId,
            before: $before,
            after: $after,
            metadata: $metadata
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeClaim(RecordClaim $claim): array
    {
        return $this->repository->decorateRow($claim->toArray());
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    private function secondsBetween(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return max(0, $end->getTimestamp() - $start->getTimestamp());
    }
}
