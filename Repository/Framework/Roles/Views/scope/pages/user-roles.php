<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;
use Catalyst\Repository\Roles\Support\RbacLabelPresenter;

return static function (array $scope): array {
    $user = is_array($scope['user'] ?? null) ? $scope['user'] : [];
    $allRoles = array_values((array) ($scope['allRoles'] ?? []));
    $userRoles = is_array($scope['userRoles'] ?? null) ? $scope['userRoles'] : [];
    $rows = [];

    foreach ($allRoles as $role) {
        $role = is_array($role) ? $role : [];
        $roleId = (int) ($role['id'] ?? 0);
        $hasRole = isset($userRoles[$roleId]);
        $rows[] = [
            'role_name' => RbacLabelPresenter::roleName((string) ($role['name'] ?? ''), (string) ($role['slug'] ?? '')),
            'slug' => (string) ($role['slug'] ?? ''),
            'status_badge_class' => $hasRole ? 'bg-success' : 'bg-secondary',
            'status_label' => $hasRole ? __('roles.user_roles.assigned') : __('roles.user_roles.not_assigned'),
            'action_url' => $hasRole
                ? '/users/' . (int) ($user['id'] ?? 0) . '/roles/' . $roleId . '/remove'
                : '/users/' . (int) ($user['id'] ?? 0) . '/roles/' . $roleId,
            'action_class' => $hasRole ? 'btn-outline-danger btn-sm' : 'btn-outline-primary btn-sm',
            'action_label' => $hasRole ? __('roles.user_roles.remove') : __('roles.user_roles.assign'),
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('roles.users.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.user_roles.title')),
            'description' => (string) ($user['email'] ?? ''),
            'actions' => [
                ['label' => __('roles.users.back_to_users'), 'href' => '/users', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],

        'user_email' => (string) ($user['email'] ?? ''),
        'roles_rows' => $rows,
        'has_roles' => $rows !== [],
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
