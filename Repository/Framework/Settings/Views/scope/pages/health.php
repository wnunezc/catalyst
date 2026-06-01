<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;

return static function (array $scope): array {
    $health = is_array($scope['health'] ?? null) ? $scope['health'] : [];
    $summary = is_array($health['summary'] ?? null) ? $health['summary'] : ['checks' => 0, 'warnings' => 0, 'failures' => 0, 'route_issues' => 0];
    $sections = [
        'runtime' => __('settings.health.sections.runtime'),
        'platform' => __('settings.health.sections.platform'),
        'session' => __('settings.health.sections.session'),
        'cache' => __('settings.health.sections.cache'),
        'queue' => __('settings.health.sections.queue'),
        'scheduler' => __('settings.health.sections.scheduler'),
        'storage' => __('settings.health.sections.storage'),
        'secrets' => __('settings.health.sections.secrets'),
        'throttling' => __('settings.health.sections.throttling'),
    ];

    $badgeClass = static function (string $status): string {
        return match ($status) {
            'ok' => 'text-bg-success',
            'warn' => 'text-bg-warning',
            'fail' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    };

    $statusLabel = static function (string $status): string {
        return match ($status) {
            'ok' => __('settings.health.status.ok'),
            'warn' => __('settings.health.status.warn'),
            'fail' => __('settings.health.status.fail'),
            default => strtoupper($status),
        };
    };

    $health['ready_badge_class'] = !empty($health['ready']) ? 'text-bg-success' : 'text-bg-warning';
    $health['ready_label'] = !empty($health['ready'])
        ? __('settings.health.status.ready')
        : __('settings.health.status.not_ready');
    $health['environment_label'] = (string) ($health['environment'] ?? __('settings.health.status.unknown'));
    $health['generated_at_label'] = sprintf(__('settings.health.generated_at'), (string) ($health['generated_at'] ?? ''));

    $routeContract = is_array($health['route_contract'] ?? null) ? $health['route_contract'] : [];
    $routeContract['badge_class'] = !empty($routeContract['ok']) ? 'text-bg-success' : 'text-bg-danger';
    $routeContract['badge_label'] = !empty($routeContract['ok'])
        ? __('settings.health.route_contract.ok')
        : __('settings.health.route_contract.issues');
    $routeContract['checks'] = array_map(
        static function (string $name, array $check) use ($badgeClass): array {
            $ok = !empty($check['ok']);

            return [
                'label' => __('settings.health.route_contract.checks.' . $name),
                'badge_class' => $ok ? 'text-bg-success' : 'text-bg-danger',
                'badge_label' => $ok
                    ? __('settings.health.route_contract.ok')
                    : __('settings.health.route_contract.fail'),
                'checked_label' => sprintf(__('settings.health.route_contract.checked'), (string) ($check['checked'] ?? 0)),
            ];
        },
        array_keys((array) ($routeContract['checks'] ?? [])),
        array_values((array) ($routeContract['checks'] ?? []))
    );
    $routeContract['issues'] = array_map(
        static fn (array $issue): array => [
            'type_label' => (string) ($issue['type'] ?? __('settings.health.route_contract.issue')),
            'message' => (string) ($issue['message'] ?? ''),
        ],
        array_values(array_filter((array) ($routeContract['issues'] ?? []), 'is_array'))
    );

    $sectionsView = [];
    foreach ($sections as $key => $label) {
        $sectionsView[] = [
            'label' => $label,
            'checks' => array_map(
                static fn (array $check): array => array_merge($check, [
                    'badge_class' => $badgeClass((string) ($check['status'] ?? '')),
                    'status_label' => $statusLabel((string) ($check['status'] ?? '')),
                ]),
                array_values(array_filter((array) ($health[$key] ?? []), 'is_array'))
            ),
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('settings.health.title'),
            'title' => (string) ($scope['pageTitle'] ?? $scope['title'] ?? __('settings.health.title')),
            'description' => $health['generated_at_label'],
            'metrics' => [
                ['label' => __('settings.health.cards.readiness'), 'value' => $health['ready_label'], 'badge_class' => $health['ready_badge_class']],
                ['label' => 'Environment', 'value' => $health['environment_label']],
                ['label' => __('settings.health.cards.checks'), 'value' => (string) ($summary['checks'] ?? 0)],
                ['label' => __('settings.health.cards.warnings'), 'value' => (string) ($summary['warnings'] ?? 0), 'value_class' => 'text-warning'],
                ['label' => __('settings.health.cards.failures'), 'value' => (string) ($summary['failures'] ?? 0), 'value_class' => 'text-danger'],
            ],
        ],

        'title' => (string) ($scope['title'] ?? __('settings.health.title')),
        'pageTitle' => (string) ($scope['pageTitle'] ?? $scope['title'] ?? __('settings.health.title')),
        'health' => $health,
        'summary' => $summary,
        'routeContract' => $routeContract,
        'sectionsView' => $sectionsView,
    ];
};
