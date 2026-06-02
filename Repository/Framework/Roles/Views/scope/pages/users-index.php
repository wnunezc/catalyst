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

use Catalyst\Repository\Roles\Support\RbacLabelPresenter;

return static function (array $scope): array {
    $users = array_values((array) ($scope['users'] ?? []));
    $rows = [];

    foreach ($users as $user) {
        $user = is_array($user) ? $user : [];
        $hasRoles = !empty($user['roles']);
        $rows[] = [
            'id' => (int) ($user['id'] ?? 0),
            'name' => (string) ($user['name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'roles_badge_class' => $hasRoles ? 'text-bg-light border' : 'text-bg-warning',
            'roles_label' => $hasRoles
                ? RbacLabelPresenter::roleList((string) $user['roles'])
                : __('roles.users.no_role'),
            'status_badge_class' => (int) ($user['active'] ?? 0) === 1 ? 'text-bg-success' : 'text-bg-secondary',
            'status_label' => (int) ($user['active'] ?? 0) === 1
                ? __('roles.users.status.active')
                : __('roles.users.status.inactive'),
            'verification_badge_class' => (int) ($user['email_verified'] ?? 0) === 1 ? 'text-bg-success' : 'text-bg-warning',
            'verification_label' => (int) ($user['email_verified'] ?? 0) === 1
                ? __('roles.users.verification.verified')
                : __('roles.users.verification.pending'),
            'created_at' => (string) ($user['created_at'] ?? ''),
            'roles_url' => '/users/' . (int) ($user['id'] ?? 0) . '/roles',
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('roles.users.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.users.title')),
            'description' => __('roles.users.hero_lede'),
            'actions' => [
                ['label' => __('roles.users.register_title'), 'href' => '/users/enroll', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-user-plus'],
            ],
        ],

        'has_users' => $rows !== [],
        'users_rows' => $rows,
        'record_count_label' => sprintf(__('roles.users.record_count'), count($rows)),
    ];
};
