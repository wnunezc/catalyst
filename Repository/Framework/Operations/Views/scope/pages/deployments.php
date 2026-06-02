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
