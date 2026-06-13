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
use Catalyst\Repository\Users\Support\RbacLabelPresenter;

return static function (array $scope): array {
    $recordPresence = is_array($scope['recordPresence'] ?? null) ? $scope['recordPresence'] : [];
    $role = is_array($scope['role'] ?? null) ? $scope['role'] : [];
    $rolePermissions = is_array($scope['rolePermissions'] ?? null) ? $scope['rolePermissions'] : [];
    $allPermissions = is_array($scope['allPermissions'] ?? null) ? $scope['allPermissions'] : [];

    $permissionRows = array_map(
        static function (array $perm) use ($rolePermissions): array {
            $permissionId = (int) ($perm['id'] ?? 0);

            return [
                'input_id' => 'perm_' . $permissionId,
                'value' => $permissionId,
                'checked' => isset($rolePermissions[$permissionId]),
                'display_name' => RbacLabelPresenter::permissionName(
                    (string) ($perm['name'] ?? ''),
                    (string) ($perm['slug'] ?? '')
                ),
                'slug' => (string) ($perm['slug'] ?? ''),
            ];
        },
        $allPermissions
    );

    return [
        'page_header' => [
            'eyebrow' => __('roles.permissions.map_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.roles.permissions_page_title')),
            'description' => __('roles.permissions.role_label') . ' ' . (string) ($role['slug'] ?? ''),
            'actions' => [
                ['label' => __('roles.common.back_to_roles'), 'href' => '/users/roles', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],

        'role' => $role,
        'claimToken' => (string) ($recordPresence['claim_token'] ?? ''),
        'permissionsAction' => '/users/roles/' . (int) ($role['id'] ?? 0) . '/permissions',
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'permissionRows' => $permissionRows,
        'availablePermissionCountLabel' => sprintf(__('roles.permissions.available_count'), count($permissionRows)),
    ];
};
