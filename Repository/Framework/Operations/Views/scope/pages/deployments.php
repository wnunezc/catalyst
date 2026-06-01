<?php

declare(strict_types=1);

return static function (array $scope): array {
    $profiles = [];

    foreach ((array) ($scope['deploymentProfiles'] ?? []) as $key => $profile) {
        $profile = is_array($profile) ? $profile : [];
        $profiles[] = [
            'key' => (string) $key,
            'description' => (string) ($profile['description'] ?? ''),
            'create_zip_value' => !empty($profile['create_zip']) ? __('ui.common.yes') : __('ui.common.no'),
            'publish_remote_value' => !empty($profile['publish_remote']) ? __('ui.common.yes') : __('ui.common.no'),
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('operations.deployments.title'),
            'title' => (string) ($scope['pageTitle'] ?? __('operations.deployments.title')),
            'description' => __('operations.deployments.form.description'),
        ],

        'form' => (array) ($scope['deploymentForm'] ?? []),
        'grid' => (array) ($scope['deploymentGrid'] ?? []),
        'deployment_profiles' => $profiles,
        'create_zip_label' => __('operations.deployments.profiles.create_zip'),
        'publish_remote_label' => __('operations.deployments.profiles.publish_remote'),
    ];
};
