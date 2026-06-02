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

namespace Catalyst\Framework\Traits;

use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Http\Request;
use RuntimeException;
use Throwable;

/**
 * Defines the Interacts With Record Claims Trait trait contract.
 *
 * @package Catalyst\Framework\Traits
 * Responsibility: Coordinates the interacts with record claims trait behavior within its module boundary.
 */
trait InteractsWithRecordClaimsTrait
{
    /**
     * Handles the acquire record claim workflow.
     */
    protected function acquireRecordClaim(string $resourceKey, int $recordId, array $metadata = []): array
    {
        return RecordClaimManager::getInstance()->acquire(
            resourceKey: $resourceKey,
            recordId: $recordId,
            metadata: $metadata
        );
    }

    /**
     * Handles the assert record claim available workflow.
     */
    protected function assertRecordClaimAvailable(string $resourceKey, int $recordId, Request $request): ?array
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        return RecordClaimManager::getInstance()->assertAvailable(
            resourceKey: $resourceKey,
            recordId: $recordId,
            claimToken: $claimToken !== '' ? $claimToken : null
        );
    }

    /**
     * Handles the release record claim workflow.
     */
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

    /**
     * Handles the remember concurrency conflict workflow.
     */
    protected function rememberConcurrencyConflict(Request $request, RuntimeException $e, string $bag = 'default'): void
    {
        $this->rememberValidationState($request->all(), [
            'lock_version' => [$e->getMessage()],
        ], $bag);
    }
}
