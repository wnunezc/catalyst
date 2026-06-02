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

return static function (array $scope): array {
    $entry = is_array($scope['entry'] ?? null) ? $scope['entry'] : [];
    $encode = static fn (mixed $value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';

    return [
        'admin_header' => [
            'eyebrow' => __('audit.index.title'),
            'title' => __('audit.show.title_prefix') . ' #' . (string) ($entry['id'] ?? 0),
            'description' => (string) ($entry['resource'] ?? '') . ' · ' . (string) ($entry['action'] ?? ''),
            'actions' => [
                ['label' => __('audit.show.back'), 'href' => '/audit-log', 'class' => 'btn btn-sm btn-outline-secondary'],
            ],
        ],

        'title_label' => __('audit.show.title_prefix'),
        'entry' => [
            'id' => (int) ($entry['id'] ?? 0),
            'resource' => (string) ($entry['resource'] ?? ''),
            'action' => (string) ($entry['action'] ?? ''),
            'occurred_at' => (string) ($entry['occurred_at'] ?? ''),
            'actor_type_label' => (string) ($entry['actor_type'] ?? __('audit.show.common.system')),
            'actor_id_label' => (string) (($entry['actor_id'] ?? null) !== null ? '#' . $entry['actor_id'] : __('audit.show.common.not_available')),
            'request_method' => (string) ($entry['request_method'] ?? ''),
            'request_uri_label' => (string) ($entry['request_uri'] ?? ($entry['event_name'] ?? '')),
            'tenant_key_label' => (string) ($entry['tenant_key'] ?? __('audit.show.common.default_tenant')),
            'tenant_id_label' => (string) ($entry['tenant_id'] ?? '0'),
        ],
        'before_payload_json' => $encode($entry['before_payload'] ?? []),
        'after_payload_json' => $encode($entry['after_payload'] ?? []),
        'metadata_json' => $encode($entry['metadata'] ?? []),
    ];
};
