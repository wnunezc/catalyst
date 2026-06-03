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

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $catalogRows = [];
    $summary = (array) ($scope['summary'] ?? []);

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