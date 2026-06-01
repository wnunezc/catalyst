<?php

declare(strict_types=1);

namespace Catalyst\Framework\Idempotency;

use Catalyst\Entities\IdempotencyKey;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;

final class IdempotencyRepository
{
    use SingletonTrait;

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

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
