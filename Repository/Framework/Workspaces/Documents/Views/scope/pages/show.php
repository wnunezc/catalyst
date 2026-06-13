<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $template = is_array($scope['template'] ?? null) ? $scope['template'] : [];
    $preview = is_array($scope['preview'] ?? null) ? $scope['preview'] : null;
    $recordPresence = is_array($scope['recordPresence'] ?? null) ? $scope['recordPresence'] : [];

    $availableTransitions = [];
    foreach ((array) ($scope['availableTransitions'] ?? []) as $transition) {
        $transition = is_array($transition) ? $transition : [];
        if (empty($transition['allowed'])) {
            continue;
        }

        $availableTransitions[] = [
            'key' => (string) ($transition['key'] ?? ''),
            'label' => (string) ($transition['label'] ?? $transition['key'] ?? __('documents.show.workflow.transition')),
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
            'actor_label' => (string) ($transition['actor_id'] ?? __('documents.show.common.system')),
        ];
    }

    $versionsRows = [];
    foreach ((array) ($scope['versions'] ?? []) as $version) {
        $version = is_array($version) ? $version : [];
        $versionsRows[] = [
            'version_number' => (int) ($version['version_number'] ?? 0),
            'summary' => (string) ($version['summary'] ?? ''),
            'created_at' => (string) ($version['created_at'] ?? ''),
            'restore_url' => '/workspaces/document-templates/' . (int) ($template['id'] ?? 0) . '/versions/' . (int) ($version['id'] ?? 0) . '/restore',
        ];
    }

    $artifactsRows = [];
    foreach ((array) ($scope['artifacts'] ?? []) as $artifact) {
        $artifact = is_array($artifact) ? $artifact : [];
        $artifactsRows[] = [
            'name' => (string) ($artifact['name'] ?? ''),
            'format' => (string) ($artifact['format'] ?? ''),
            'created_at' => (string) ($artifact['created_at'] ?? ''),
            'public_url' => (string) ($artifact['public_url'] ?? '#'),
        ];
    }

    return [
        'page_header' => [
            'eyebrow' => __('documents.show.eyebrow'),
            'title' => (string) ($template['name'] ?? 'Document Template'),
            'description' => (string) ($template['description'] ?? ''),
            'actions' => [
                ['label' => __('documents.common.edit'), 'href' => '/workspaces/document-templates/' . (int) ($template['id'] ?? 0) . '/edit', 'class' => 'btn btn-sm btn-outline-primary', 'icon' => 'fa-solid fa-pen'],
                ['label' => __('documents.common.back'), 'href' => '/workspaces/document-templates', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
            'metrics' => [
                ['label' => __('documents.show.stats.workflow'), 'value' => (string) ($template['current_state'] ?? 'draft')],
                ['label' => __('documents.show.stats.format'), 'value' => (string) ($template['format'] ?? '')],
                ['label' => __('documents.show.stats.slug'), 'value' => (string) ($template['slug'] ?? '')],
            ],
        ],

        'template' => [
            'id' => (int) ($template['id'] ?? 0),
            'name' => (string) ($template['name'] ?? __('documents.show.template_fallback')),
            'description' => (string) ($template['description'] ?? __('documents.show.lede_fallback')),
            'current_state' => (string) ($template['current_state'] ?? 'draft'),
            'format' => (string) ($template['format'] ?? 'html'),
            'slug' => (string) ($template['slug'] ?? ''),
            'edit_url' => '/workspaces/document-templates/' . (int) ($template['id'] ?? 0) . '/edit',
            'transition_url' => '/workspaces/document-templates/' . (int) ($template['id'] ?? 0) . '/transition',
            'preview_url' => '/workspaces/document-templates/' . (int) ($template['id'] ?? 0) . '/preview',
            'export_url' => '/workspaces/document-templates/' . (int) ($template['id'] ?? 0) . '/export',
        ],
        'claim_token' => (string) ($recordPresence['claim_token'] ?? ''),
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'available_transitions' => $availableTransitions,
        'transitions_rows' => $transitionsRows,
        'preview_payload_json' => (string) ($scope['previewPayloadJson'] ?? ''),
        'has_preview' => $preview !== null,
        'preview' => $preview === null ? null : [
            'checksum_sha256' => (string) ($preview['checksum_sha256'] ?? ''),
            'format_is_html' => (string) ($template['format'] ?? 'html') === 'html',
            'content_html' => TrustedHtml::fromString((string) ($preview['content'] ?? '')),
            'display_content' => (string) ($preview['display_content'] ?? $preview['content'] ?? ''),
        ],
        'versions_rows' => $versionsRows,
        'artifacts_rows' => $artifactsRows,
    ];
};
