<?php

declare(strict_types=1);

namespace Catalyst\Framework\Traits;

use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Http\Request;
use RuntimeException;
use Throwable;

trait InteractsWithRecordClaimsTrait
{
    protected function acquireRecordClaim(string $resourceKey, int $recordId, array $metadata = []): array
    {
        return RecordClaimManager::getInstance()->acquire(
            resourceKey: $resourceKey,
            recordId: $recordId,
            metadata: $metadata
        );
    }

    protected function assertRecordClaimAvailable(string $resourceKey, int $recordId, Request $request): ?array
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        return RecordClaimManager::getInstance()->assertAvailable(
            resourceKey: $resourceKey,
            recordId: $recordId,
            claimToken: $claimToken !== '' ? $claimToken : null
        );
    }

    protected function releaseRecordClaim(string $resourceKey, int $recordId, Request $request, ?string $reason = null): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        try {
            RecordClaimManager::getInstance()->release(
                resourceKey: $resourceKey,
                recordId: $recordId,
                reason: $reason,
                claimToken: $claimToken !== '' ? $claimToken : null
            );
        } catch (Throwable $e) {
            $this->logWarning('Claim release skipped after mutation.', [
                'resource_key' => $resourceKey,
                'record_id' => $recordId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, mixed>|null $claim
     * @return array<string, mixed>|null
     */
    protected function buildRecordClaimContext(?array $claim): ?array
    {
        if ($claim === null) {
            return null;
        }

        return [
            'tenant_id' => (int) ($claim['tenant_id'] ?? 0),
            'tenant_key' => (string) ($claim['tenant_key'] ?? ''),
            'resource_key' => (string) ($claim['resource_key'] ?? ''),
            'record_id' => (int) ($claim['record_id'] ?? 0),
            'status' => (string) ($claim['status'] ?? 'active'),
            'claimed_by_label' => (string) ($claim['claimed_by_label'] ?? ''),
            'claimed_by' => ($claim['claimed_by'] ?? null) !== null ? (int) $claim['claimed_by'] : null,
            'claimed_at' => (string) ($claim['claimed_at'] ?? ''),
            'expires_at' => (string) ($claim['expires_at'] ?? ''),
            'released_at' => (string) ($claim['released_at'] ?? ''),
            'seconds_to_expiry' => ($claim['seconds_to_expiry'] ?? null) !== null ? (int) $claim['seconds_to_expiry'] : null,
            'claim_token' => (string) ($claim['claim_token'] ?? ''),
            'is_owner' => RecordClaimManager::getInstance()->owns($claim),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function concurrencyHiddenFields(?array $claim, ?int $lockVersion = null): array
    {
        $fields = [];

        if ($claim !== null && trim((string) ($claim['claim_token'] ?? '')) !== '') {
            $fields['claim_token'] = [
                'name' => 'claim_token',
                'type' => 'hidden',
                'value' => (string) $claim['claim_token'],
            ];
        }

        if ($lockVersion !== null && $lockVersion > 0) {
            $fields['lock_version'] = [
                'name' => 'lock_version',
                'type' => 'hidden',
                'value' => (string) $lockVersion,
            ];
        }

        return $fields;
    }

    protected function rememberConcurrencyConflict(Request $request, RuntimeException $e, string $bag = 'default'): void
    {
        $this->rememberValidationState($request->all(), [
            'lock_version' => [$e->getMessage()],
        ], $bag);
    }
}
