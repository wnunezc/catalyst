<?php

declare(strict_types=1);

return static function (array $scope): array {
    $featureSummary = is_array($scope['featureSummary'] ?? null) ? $scope['featureSummary'] : [];
    $pluginSummary = is_array($scope['pluginSummary'] ?? null) ? $scope['pluginSummary'] : [];
    $deploymentSummary = is_array($scope['deploymentSummary'] ?? null) ? $scope['deploymentSummary'] : [];
    $recentRuns = array_map(
        static fn (array $run): array => array_merge($run, [
            'status_label' => (string) ($run['status'] ?? __('operations.index.common.unknown')),
            'started_at_label' => (string) ($run['started_at'] ?? '—'),
        ]),
        is_array($scope['recentRuns'] ?? null) ? $scope['recentRuns'] : []
    );
    $tenancySummary = is_array($scope['tenancySummary'] ?? null) ? $scope['tenancySummary'] : [];
    $appearanceSummary = is_array($scope['appearanceSummary'] ?? null) ? $scope['appearanceSummary'] : [];
    $localizationSummary = is_array($scope['localizationSummary'] ?? null) ? $scope['localizationSummary'] : [];

    $localizationSummary['available_locale_count'] = count((array) ($localizationSummary['available_locales'] ?? []));
    $appearanceSummary['watermark_status_label'] = !empty($appearanceSummary['watermark_enabled'])
        ? __('operations.index.common.enabled')
        : __('operations.index.common.disabled');
    $deploymentSummary['profile_count'] = count((array) ($deploymentSummary['profiles'] ?? []));
    $tenancySummary['isolation_active_label'] = !empty($tenancySummary['data_isolation_active'])
        ? __('operations.index.common.yes')
        : __('operations.index.common.no');

    return [
        'admin_header' => [
            'eyebrow' => __('operations.index.hero_eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? __('operations.title')),
            'description' => __('operations.index.hero_lede'),
            'metrics' => [
                ['label' => __('operations.index.metrics.flags'), 'value' => (string) ($featureSummary['count'] ?? 0)],
                ['label' => __('operations.index.metrics.plugins'), 'value' => (string) ($pluginSummary['enabled'] ?? 0)],
                ['label' => __('operations.index.metrics.deployments'), 'value' => (string) ($deploymentSummary['run_count'] ?? 0)],
                ['label' => __('operations.index.metrics.tenancy'), 'value' => (string) ($tenancySummary['isolation_active_label'] ?? '—')],
            ],
        ],

        'featureSummary' => $featureSummary,
        'pluginSummary' => $pluginSummary,
        'deploymentSummary' => $deploymentSummary,
        'recentRuns' => $recentRuns,
        'tenancySummary' => $tenancySummary,
        'appearanceSummary' => $appearanceSummary,
        'localizationSummary' => $localizationSummary,
    ];
};
