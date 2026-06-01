<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;

return [
    'description' => __('catalogs.module.description'),
    'routes' => [
        'web' => [
            '/workspaces/catalogs',
            '/workspaces/catalogs/create',
            '/workspaces/catalogs/{id}',
            '/workspaces/catalogs/{id}/edit',
            '/workspaces/catalogs/{id}/items/create',
            '/workspaces/catalogs/{id}/items/{itemId}/edit',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/workspaces/catalogs',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/workspaces/catalogs'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-catalogs',
            'label' => __('catalogs.module.permission_label'),
            'description' => __('catalogs.module.permission_description'),
            'action' => 'manage',
            'resource' => 'catalogs',
            'role_fallback_any' => ['admin'],
        ],
    ],
    'navigation' => [
        'admin' => [
            [
                'context' => 'workspaces',
                'label' => __('catalogs.module.navigation_label'),
                'href' => '/workspaces/catalogs',
                'icon' => 'ti ti-books',
                'matches' => ['/workspaces/catalogs'],
                'group' => 'catalogs',
                'group_label' => 'Catalogs',
                'group_order' => 20,
                'hint' => __('catalogs.module.navigation_hint'),
                'order' => 58,
                'visibility' => [
                    ['permissions_any' => ['manage-catalogs']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/workspaces/catalogs/create',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_create'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/{id}/edit',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_edit'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/{id}/items/create',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_create_item'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/{id}/items/{itemId}/edit',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_edit_item'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/{id}',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_show'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
