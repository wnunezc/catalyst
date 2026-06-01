<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $catalogRows = [];

    foreach ((array) ($scope['catalogRows'] ?? []) as $row) {
        $row = is_array($row) ? $row : [];
        $scopeKey = 'operations.feature_flags.catalog.scopes.' . str_replace('-', '_', (string) ($row['scope'] ?? 'runtime'));
        $enabled = !empty($row['enabled']);
        $readOnly = !empty($row['read_only']);

        $catalogRows[] = [
            'label' => (string) ($row['label'] ?? ''),
            'key' => (string) ($row['key'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'has_description' => (string) ($row['description'] ?? '') !== '',
            'scope_label' => __($scopeKey),
            'managed_by' => (string) ($row['managed_by'] ?? 'features.json'),
            'state_badge_class' => $enabled ? 'text-bg-success' : 'text-bg-secondary',
            'state_label' => $enabled
                ? __('operations.feature_flags.common.enabled')
                : __('operations.feature_flags.common.disabled'),
            'read_only' => $readOnly,
            'read_only_label' => __('operations.feature_flags.catalog.read_only'),
            'toggle_url' => '/configuration/feature-flags/defaults/' . rawurlencode((string) ($row['key'] ?? '')),
            'toggle_enabled_value' => $enabled ? '0' : '1',
            'toggle_button_class' => $enabled ? 'btn-outline-danger' : 'btn-outline-primary',
            'toggle_label' => $enabled
                ? __('operations.feature_flags.catalog.actions.disable')
                : __('operations.feature_flags.catalog.actions.enable'),
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('operations.feature_flags.title'),
            'title' => (string) ($scope['pageTitle'] ?? __('operations.feature_flags.title')),
            'description' => __('operations.feature_flags.hero_lede'),
            'metrics' => [
                ['label' => __('operations.index.metrics.flags'), 'value' => (string) ($summary['count'] ?? 0)],
                ['label' => __('operations.feature_flags.common.enabled'), 'value' => (string) ($summary['enabled'] ?? 0)],
                ['label' => __('operations.feature_flags.common.disabled'), 'value' => (string) ($summary['disabled'] ?? 0)],
            ],
        ],

        'summary' => [
            'count' => (int) (($scope['summary']['count'] ?? 0)),
            'enabled' => (int) (($scope['summary']['enabled'] ?? 0)),
            'disabled' => (int) (($scope['summary']['disabled'] ?? 0)),
        ],
        'catalog_rows' => $catalogRows,
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'form' => (array) ($scope['overrideForm'] ?? []),
        'grid' => (array) ($scope['overrideGrid'] ?? []),
    ];
};
