<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $rule = is_array($scope['rule'] ?? null) ? $scope['rule'] : [];
    $claimContext = is_array($scope['claimContext'] ?? null) ? $scope['claimContext'] : [];

    $availableTransitions = [];
    foreach ((array) ($scope['availableTransitions'] ?? []) as $transition) {
        $transition = is_array($transition) ? $transition : [];
        if (empty($transition['allowed'])) {
            continue;
        }

        $availableTransitions[] = [
            'key' => (string) ($transition['key'] ?? ''),
            'label' => (string) ($transition['label'] ?? $transition['key'] ?? __('automation.show.workflow.transition')),
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
            'actor_label' => (string) (($transition['actor_id'] ?? null) ?: __('automation.show.common.system')),
        ];
    }

    $versionsRows = [];
    foreach ((array) ($scope['versions'] ?? []) as $version) {
        $version = is_array($version) ? $version : [];
        $versionsRows[] = [
            'version_number' => (int) ($version['version_number'] ?? 0),
            'summary' => (string) ($version['summary'] ?? ''),
            'created_at' => (string) ($version['created_at'] ?? ''),
            'restore_url' => '/automation-rules/' . (int) ($rule['id'] ?? 0) . '/versions/' . (int) ($version['id'] ?? 0) . '/restore',
        ];
    }

    $logsRows = [];
    foreach ((array) ($scope['logs'] ?? []) as $log) {
        $log = is_array($log) ? $log : [];
        $logsRows[] = [
            'created_at' => (string) ($log['created_at'] ?? ''),
            'source_label' => (string) (($log['trigger_source'] ?? '') . (($log['event_name'] ?? '') !== '' ? ' / ' . $log['event_name'] : '')),
            'status' => (string) ($log['status'] ?? ''),
            'message' => (string) ($log['message'] ?? ''),
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('automation.show.eyebrow'),
            'title' => (string) ($rule['name'] ?? __('automation.show.rule_fallback')),
            'description' => (string) (($rule['description'] ?? '') ?: __('automation.show.lede_fallback')),
            'actions' => [
                ['label' => __('automation.show.actions.edit'), 'href' => '/automation-rules/' . (int) ($rule['id'] ?? 0) . '/edit', 'class' => 'btn btn-sm btn-outline-primary', 'icon' => 'fa-solid fa-pen'],
                ['label' => __('automation.show.actions.back'), 'href' => '/automation-rules', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
            'metrics' => [
                ['label' => __('automation.show.stats.workflow'), 'value' => (string) ($rule['current_state'] ?? 'draft')],
                ['label' => __('automation.show.stats.trigger'), 'value' => (string) ($rule['trigger_type'] ?? 'event')],
                ['label' => __('automation.show.stats.action'), 'value' => (string) ($rule['action_type'] ?? 'notification')],
                ['label' => __('automation.show.stats.last_run'), 'value' => (string) (($rule['last_run_at'] ?? null) ?: __('automation.show.common.never'))],
            ],
        ],

        'rule' => [
            'id' => (int) ($rule['id'] ?? 0),
            'name' => (string) ($rule['name'] ?? __('automation.show.rule_fallback')),
            'description' => (string) ($rule['description'] ?? __('automation.show.lede_fallback')),
            'current_state' => (string) ($rule['current_state'] ?? 'draft'),
            'trigger_type' => (string) ($rule['trigger_type'] ?? 'event'),
            'action_type' => (string) ($rule['action_type'] ?? 'notification'),
            'last_run_label' => (string) (($rule['last_run_at'] ?? null) ?: __('automation.show.common.never')),
            'temporal_state' => (string) ($rule['temporal_state'] ?? 'active'),
            'valid_from_label' => (string) (($rule['valid_from'] ?? null) ?: __('automation.show.common.now')),
            'valid_to_label' => (string) (($rule['valid_to'] ?? null) ?: __('automation.show.common.open')),
            'edit_url' => '/automation-rules/' . (int) ($rule['id'] ?? 0) . '/edit',
            'transition_url' => '/automation-rules/' . (int) ($rule['id'] ?? 0) . '/transition',
            'run_url' => '/automation-rules/' . (int) ($rule['id'] ?? 0) . '/run',
        ],
        'claim_token' => (string) ($claimContext['claim_token'] ?? ''),
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'available_transitions' => $availableTransitions,
        'transitions_rows' => $transitionsRows,
        'run_context_json' => (string) ($scope['runContextJson'] ?? ''),
        'run_idempotency_key' => (string) ($scope['runIdempotencyKey'] ?? ''),
        'has_last_run_result' => is_array($scope['lastRunResult'] ?? null),
        'last_run_result_json' => json_encode(
            $scope['lastRunResult'] ?? [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '{}',
        'versions_rows' => $versionsRows,
        'logs_rows' => $logsRows,
    ];
};
