<?php

declare(strict_types=1);

return static function (array $scope): array {
    $diagnostic = is_array($scope['diagnostic'] ?? null) ? $scope['diagnostic'] : [];
    $runtime = is_array($diagnostic['runtime'] ?? null) ? $diagnostic['runtime'] : [];
    $resolution = is_array($diagnostic['resolution'] ?? null) ? $diagnostic['resolution'] : [];
    $resolved = !empty($resolution['resolved']);

    return [
        'page_header' => [
            'eyebrow' => __('operations.tenancy.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? __('operations.tenancy.title')),
            'description' => __('operations.tenancy.description'),
        ],
        'runtime' => [
            'strategy' => (string) ($runtime['strategy'] ?? 'unknown'),
            'target_strategy' => (string) ($runtime['target_strategy'] ?? 'unknown'),
            'resolution_mode' => (string) ($runtime['resolution_mode'] ?? 'unknown'),
            'isolation_label' => !empty($runtime['data_isolation_active']) ? __('ui.common.yes') : __('ui.common.no'),
            'tenant_count' => (int) ($runtime['tenant_count'] ?? 0),
        ],
        'resolution' => [
            'status_label' => $resolved ? __('operations.tenancy.resolution.resolved') : __('operations.tenancy.resolution.unresolved'),
            'tenant_key' => $resolved ? (string) ($resolution['tenant_key'] ?? '') : __('operations.tenancy.resolution.unresolved'),
            'tenant_id' => $resolved ? (string) ($resolution['tenant_id'] ?? '') : __('operations.tenancy.resolution.unresolved'),
            'resolved_from' => (string) ($resolution['resolved_from'] ?? 'unresolved'),
            'baseline_status' => (string) ($resolution['baseline_status'] ?? 'unknown'),
        ],
    ];
};
