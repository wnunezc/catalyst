<?php

declare(strict_types=1);

return static function (array $scope): array {
    $summary = is_array($scope['summary'] ?? null) ? $scope['summary'] : [];
    $resolution = is_array($scope['resolution'] ?? null) ? $scope['resolution'] : [];
    $resolvedTenantLabel = (string) ($resolution['tenant_label'] ?? '');
    if ($resolvedTenantLabel === '' || $resolvedTenantLabel === 'Default tenant') {
        $resolvedTenantLabel = __('operations.tenancy.defaults.default_tenant');
    }

    $tenants = [];
    foreach ((array) ($summary['tenants'] ?? []) as $tenant) {
        $tenant = is_array($tenant) ? $tenant : [];
        $tenantLabel = (string) ($tenant['tenant_label'] ?? '');
        if (
            $tenantLabel === ''
            || $tenantLabel === 'Default tenant'
            || $tenantLabel === __('operations.tenancy.defaults.default_tenant')
        ) {
            $tenantLabel = __('operations.tenancy.defaults.default_tenant');
        }

        $tenants[] = [
            'tenant_label' => $tenantLabel,
            'tenant_key' => (string) ($tenant['tenant_key'] ?? ''),
            'tenant_id' => (int) ($tenant['tenant_id'] ?? 0),
            'hosts_label' => implode(', ', (array) ($tenant['hosts'] ?? [])),
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('operations.tenancy.title'),
            'title' => (string) ($scope['pageTitle'] ?? __('operations.tenancy.title')),
            'description' => __('operations.tenancy.hero_lede'),
            'metrics' => [
                ['label' => 'Strategy', 'value' => (string) ($summary['strategy'] ?? '—')],
                ['label' => 'Resolution', 'value' => (string) ($summary['resolution_mode'] ?? '—')],
                ['label' => 'Tenants', 'value' => (string) ($summary['tenant_count'] ?? 0)],
            ],
        ],

        'summary' => [
            'strategy' => (string) ($summary['strategy'] ?? 'shared-db-tenant-id'),
            'target_strategy' => (string) ($summary['target_strategy'] ?? 'shared-db-tenant-id'),
            'resolution_mode' => (string) ($summary['resolution_mode'] ?? 'host'),
            'data_isolation_label' => !empty($summary['data_isolation_active']) ? __('ui.common.yes') : __('ui.common.no'),
            'fallback_tenant' => (string) ($summary['fallback_tenant'] ?? 'default'),
            'tenant_count' => (int) ($summary['tenant_count'] ?? 0),
            'decision_note' => (string) ($summary['decision_note'] ?? ''),
        ],
        'resolution' => [
            'tenant_key' => (string) ($resolution['tenant_key'] ?? 'default'),
            'tenant_id' => (int) ($resolution['tenant_id'] ?? 0),
            'tenant_label' => $resolvedTenantLabel,
            'resolved_from' => (string) ($resolution['resolved_from'] ?? 'host'),
            'host' => (string) ($resolution['host'] ?? ''),
            'baseline_status' => (string) ($resolution['baseline_status'] ?? 'tenant-boundary-active'),
        ],
        'tenants' => $tenants,
    ];
};
