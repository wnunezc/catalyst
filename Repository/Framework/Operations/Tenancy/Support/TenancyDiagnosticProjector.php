<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Tenancy\Support;

/**
 * Projects a minimal read-only tenancy diagnostic without configuration secrets.
 */
final class TenancyDiagnosticProjector
{
    /**
     * @param array<string, mixed> $summary
     * @param array<string, mixed> $resolution
     * @return array<string, mixed>
     */
    public function project(array $summary, array $resolution): array
    {
        $tenantId = (int) ($resolution['tenant_id'] ?? 0);
        $tenantKey = trim((string) ($resolution['tenant_key'] ?? ''));

        return [
            'runtime' => [
                'strategy' => (string) ($summary['strategy'] ?? 'unknown'),
                'target_strategy' => (string) ($summary['target_strategy'] ?? 'unknown'),
                'resolution_mode' => (string) ($summary['resolution_mode'] ?? 'unknown'),
                'baseline_status' => (string) ($summary['baseline_status'] ?? 'unknown'),
                'data_isolation_active' => (bool) ($summary['data_isolation_active'] ?? false),
                'tenant_count' => max(0, (int) ($summary['tenant_count'] ?? 0)),
            ],
            'resolution' => [
                'resolved' => $tenantId > 0 && $tenantKey !== '',
                'tenant_id' => $tenantId > 0 ? $tenantId : null,
                'tenant_key' => $tenantKey !== '' ? $tenantKey : null,
                'resolved_from' => (string) ($resolution['resolved_from'] ?? 'unresolved'),
                'baseline_status' => (string) ($resolution['baseline_status'] ?? 'unknown'),
            ],
        ];
    }
}
