<?php

declare(strict_types=1);

return static function (array $scope): array {
    $profiles = [];
    foreach ((array) ($scope['deploymentProfiles'] ?? []) as $key => $profile) {
        $profiles[] = [
            'key' => (string) $key,
            'description' => (string) ((is_array($profile) ? $profile : [])['description'] ?? ''),
        ];
    }

    return [
        'page_header' => [
            'eyebrow' => __('operations.deployments.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? __('operations.deployments.title')),
            'description' => __('operations.deployments.description'),
        ],
        'form' => (array) ($scope['deploymentForm'] ?? []),
        'grid' => (array) ($scope['deploymentGrid'] ?? []),
        'deployment_profiles' => $profiles,
        'dry_run_ready_label' => __('operations.deployments.profiles.dry_run_ready'),
    ];
};
