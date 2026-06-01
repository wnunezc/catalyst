<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;

return [
    'description' => __('roles.module.description'),
    'routes' => [
        'web' => [
            '/users',
            '/users/enroll',
            '/users/{userId}/roles',
            '/users/roles',
            '/users/roles/create',
            '/users/roles/{id}/edit',
            '/users/roles/{id}/permissions',
            '/users/permissions',
            '/users/permissions/create',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/users',
            '/users/roles',
            '/users/permissions',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => [
                '/users',
                '/users/roles',
                '/users/permissions',
            ],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-users',
            'label' => __('roles.module.manage_users_label'),
            'description' => __('roles.module.manage_users_description'),
            'action' => 'manage',
            'resource' => 'users',
            'resources_any' => ['user-roles'],
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-roles',
            'label' => __('roles.module.manage_roles_label'),
            'description' => __('roles.module.manage_roles_description'),
            'action' => 'manage',
            'resource' => 'roles',
            'resources_any' => ['permissions', 'role-permissions'],
            'role_fallback_any' => ['admin'],
        ],
    ],
    'seeds' => [
        'roles',
        'permissions',
        'role_permissions',
        'user_roles',
    ],
    'navigation' => [
        'admin' => [
            [
                'context' => 'users',
                'label' => __('roles.module.users_label'),
                'href' => '/users',
                'icon' => 'ti ti-users',
                'matches' => ['/users'],
                'group' => 'users',
                'group_label' => 'Users',
                'group_order' => 10,
                'hint' => __('roles.module.users_hint'),
                'order' => 10,
                'children' => [
                    [
                        'label' => __('roles.module.user_register_label'),
                        'href' => '/users/enroll',
                        'icon' => 'ti ti-user-plus',
                        'matches' => ['/users/enroll'],
                        'hint' => __('roles.module.user_register_hint'),
                    ],
                ],
                'visibility' => [
                    ['permissions_any' => ['manage-users']],
                ],
            ],
            [
                'context' => 'users',
                'label' => 'Roles',
                'href' => '/users/roles',
                'icon' => 'ti ti-shield-check',
                'matches' => ['/users/roles'],
                'group' => 'users',
                'group_label' => 'Users',
                'group_order' => 10,
                'hint' => __('roles.module.roles_hint'),
                'order' => 20,
                'visibility' => [
                    ['permissions_any' => ['manage-roles']],
                ],
            ],
            [
                'context' => 'users',
                'label' => 'Permissions',
                'href' => '/users/permissions',
                'icon' => 'ti ti-key',
                'matches' => ['/users/permissions'],
                'group' => 'users',
                'group_label' => 'Users',
                'group_order' => 10,
                'hint' => __('roles.module.permissions_hint'),
                'order' => 30,
                'visibility' => [
                    ['permissions_any' => ['manage-roles']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/users/enroll',
                'trail' => [
                    ['label' => __('roles.module.users_label'), 'href' => '/users'],
                    ['label' => __('roles.module.user_register_label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/users/{userId}/roles',
                'trail' => [
                    ['label' => __('roles.module.users_label'), 'href' => '/users'],
                    ['label' => __('roles.module.user_roles_breadcrumb'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/users',
                'trail' => [
                    ['label' => __('roles.module.users_label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/users/roles/create',
                'trail' => [
                    ['label' => 'Roles', 'href' => '/users/roles'],
                    ['label' => __('roles.module.create_role_breadcrumb'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/users/roles/{id}/edit',
                'trail' => [
                    ['label' => 'Roles', 'href' => '/users/roles'],
                    ['label' => __('roles.module.edit_role_breadcrumb'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/users/roles/{id}/permissions',
                'trail' => [
                    ['label' => 'Roles', 'href' => '/users/roles'],
                    ['label' => 'Permissions', 'href' => null],
                ],
            ],
            [
                'pattern' => '/users/roles',
                'trail' => [
                    ['label' => 'Roles', 'href' => null],
                ],
            ],
            [
                'pattern' => '/users/permissions/create',
                'trail' => [
                    ['label' => 'Permissions', 'href' => '/users/permissions'],
                    ['label' => __('roles.module.create_permission_breadcrumb'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/users/permissions',
                'trail' => [
                    ['label' => __('roles.module.users_label'), 'href' => '/users'],
                    ['label' => 'Permissions', 'href' => null],
                ],
            ],
        ],
    ],
];
