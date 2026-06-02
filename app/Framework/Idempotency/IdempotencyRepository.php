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

namespace Catalyst\Framework\Idempotency;

use Catalyst\Entities\IdempotencyKey;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Defines the Idempotency Repository class contract.
 *
 * @package Catalyst\Framework\Idempotency
 * Responsibility: Coordinates the idempotency repository behavior within its module boundary.
 */
final class IdempotencyRepository
{
    use SingletonTrait;

    /**
     * Finds the requested record.
     */
    public function find(string $scopeKey, string $idempotencyKey): ?IdempotencyKey
    {
        return IdempotencyKey::query()
            ->whereEqual('tenant_id', $this->currentTenantId())
            ->whereEqual('scope_key', $scopeKey)
            ->whereEqual('idempotency_key', $idempotencyKey)
            ->first();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): IdempotencyKey
    {
        return IdempotencyKey::create([
            'tenant_id' => $this->currentTenantId(),
            'scope_key' => (string) ($attributes['scope_key'] ?? ''),
            'idempotency_key' => (string) ($attributes['idempotency_key'] ?? ''),
            'fingerprint_hash' => (string) ($attributes['fingerprint_hash'] ?? ''),
            'status' => (string) ($attributes['status'] ?? 'pending'),
            'outcome_json' => $attributes['outcome_json'] ?? null,
            'completed_at' => $attributes['completed_at'] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $outcome
     */
    public function complete(IdempotencyKey $record, string $status, array $outcome): IdempotencyKey
    {
        $record->fill([
            'status' => $status,
            'outcome_json' => $outcome,
            'completed_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $record->save();

        return $record;
    }

    /**
     * Handles the current tenant id workflow.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
