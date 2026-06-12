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
    $catalog = is_array($scope['catalog'] ?? null) ? $scope['catalog'] : [];
    $timeline = is_array($scope['timeline'] ?? null) ? $scope['timeline'] : [];
    $recordPresence = is_array($scope['recordPresence'] ?? null) ? $scope['recordPresence'] : [];

    $itemsRows = [];
    foreach ((array) ($scope['items'] ?? []) as $item) {
        $item = is_array($item) ? $item : [];
        $itemsRows[] = [
            'label' => (string) ($item['label'] ?? ''),
            'item_key' => (string) ($item['item_key'] ?? ''),
            'temporal_badge_class' => !empty($item['is_available']) ? 'text-bg-success' : 'text-bg-secondary',
            'temporal_state' => (string) ($item['temporal_state'] ?? 'active'),
            'is_disabled' => empty($item['is_enabled']),
            'valid_from_label' => (string) (($item['valid_from'] ?? null) ?: __('catalogs.show.common.now')),
            'valid_to_label' => (string) (($item['valid_to'] ?? null) ?: __('catalogs.show.common.open')),
            'metadata_json' => json_encode((array) ($item['metadata_json'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            'edit_url' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/items/' . (int) ($item['id'] ?? 0) . '/edit',
            'delete_url' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/items/' . (int) ($item['id'] ?? 0) . '/delete',
        ];
    }

    $transitionsRows = [];
    foreach ((array) ($scope['transitions'] ?? []) as $transition) {
        $transition = is_array($transition) ? $transition : [];
        $transitionsRows[] = [
            'occurred_at' => (string) ($transition['occurred_at'] ?? ''),
            'transition_key' => (string) ($transition['transition_key'] ?? ''),
            'from_state' => (string) ($transition['from_state'] ?? ''),
            'to_state' => (string) ($transition['to_state'] ?? ''),
            'actor_label' => (string) ($transition['actor_id'] ?? __('catalogs.show.common.system')),
        ];
    }

    $availableTransitions = [];
    foreach ((array) ($scope['availableTransitions'] ?? []) as $transition) {
        $transition = is_array($transition) ? $transition : [];
        if (empty($transition['allowed'])) {
            continue;
        }

        $availableTransitions[] = [
            'key' => (string) ($transition['key'] ?? ''),
            'label' => (string) ($transition['label'] ?? $transition['key'] ?? __('catalogs.show.workflow.transition')),
        ];
    }

    $timelineEvents = [];
    foreach ((array) ($timeline['events'] ?? []) as $event) {
        $event = is_array($event) ? $event : [];
        $timelineEvents[] = [
            'occurred_at' => (string) ($event['occurred_at'] ?? ''),
            'event_type' => (string) ($event['event_type'] ?? ''),
            'event_key' => (string) ($event['event_key'] ?? ''),
            'label' => (string) ($event['label'] ?? ''),
        ];
    }

    $versionsRows = [];
    foreach ((array) ($scope['versions'] ?? []) as $version) {
        $version = is_array($version) ? $version : [];
        $versionsRows[] = [
            'version_number' => (int) ($version['version_number'] ?? 0),
            'summary' => (string) ($version['summary'] ?? ''),
            'created_at' => (string) ($version['created_at'] ?? ''),
            'restore_url' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/versions/' . (int) ($version['id'] ?? 0) . '/restore',
        ];
    }

    return [
        'page_header' => [
            'eyebrow' => __('catalogs.show.eyebrow'),
            'title' => (string) ($catalog['label'] ?? __('catalogs.show.catalog_fallback')),
            'description' => (string) (($catalog['description'] ?? '') ?: __('catalogs.show.lede_fallback')),
            'actions' => [
                ['label' => __('catalogs.show.items.create'), 'href' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/items/create', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-plus'],
                ['label' => __('catalogs.common.edit'), 'href' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/edit', 'class' => 'btn btn-sm btn-outline-primary', 'icon' => 'fa-solid fa-pen'],
                ['label' => __('catalogs.common.back'), 'href' => '/workspaces/catalogs', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
            'metrics' => [
                ['label' => __('catalogs.show.stats.workflow'), 'value' => (string) ($catalog['current_state'] ?? 'draft')],
                ['label' => __('catalogs.show.stats.items'), 'value' => (string) ($catalog['item_count'] ?? 0)],
                ['label' => __('catalogs.show.stats.enabled'), 'value' => (string) ($catalog['enabled_item_count'] ?? 0)],
                ['label' => __('catalogs.show.stats.key'), 'value' => (string) ($catalog['catalog_key'] ?? '')],
            ],
        ],

        'catalog' => [
            'id' => (int) ($catalog['id'] ?? 0),
            'label' => (string) ($catalog['label'] ?? __('catalogs.show.catalog_fallback')),
            'description' => (string) (($catalog['description'] ?? '') ?: __('catalogs.show.lede_fallback')),
            'catalog_key' => (string) ($catalog['catalog_key'] ?? ''),
            'current_state' => (string) ($catalog['current_state'] ?? 'draft'),
            'enabled_item_count' => (int) ($catalog['enabled_item_count'] ?? 0),
            'item_count' => (int) ($catalog['item_count'] ?? 0),
            'create_item_url' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/items/create',
            'edit_url' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/edit',
            'transition_url' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/transition',
        ],
        'timeline' => [
            'elapsed_iso8601' => (string) ($timeline['elapsed_iso8601'] ?? 'PT0S'),
        ],
        'claim_token' => (string) ($recordPresence['claim_token'] ?? ''),
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'available_transitions' => $availableTransitions,
        'transitions_rows' => $transitionsRows,
        'items_rows' => $itemsRows,
        'timeline_events' => $timelineEvents,
        'versions_rows' => $versionsRows,
        'item_delete_confirm' => __('catalogs.show.items.confirm_delete'),
    ];
};
