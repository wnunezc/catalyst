<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;

return [
    'description' => __('apiplatform.module.description'),
    'routes' => [
        'web' => [
            '/api-platform',
        ],
        'api' => [
            '/api/v1/catalog',
            '/api/v1/workflows',
            '/api/v1/workflows/{id}/transition',
            '/api/v1/versions/{resourceKey}/{recordId}',
            '/api/v1/versions/{id}/restore',
        ],
        'aliases' => [],
        'prefixes' => [
            '/api-platform',
            '/api/v1/catalog',
            '/api/v1/workflows',
            '/api/v1/versions',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/api-platform'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/api/v1/catalog', '/api/v1/workflows', '/api/v1/versions'],
            'middleware_all' => [
                ApiTokenMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-api-platform',
            'label' => __('apiplatform.module.permission_label'),
            'description' => __('apiplatform.module.permission_description'),
            'action' => 'manage',
            'resource' => 'api-platform',
            'abilities_any' => ['view-any', 'create', 'revoke'],
            'role_fallback_any' => ['admin'],
        ],
    ],
    'navigation' => [
        'admin' => [
            [
                'context' => 'operations',
                'label' => __('apiplatform.module.navigation_label'),
                'href' => '/api-platform',
                'icon' => 'ti ti-api',
                'matches' => ['/api-platform'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('apiplatform.module.navigation_hint'),
                'order' => 70,
                'visibility' => [
                    ['permissions_any' => ['manage-api-platform']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/api-platform',
                'trail' => [
                    ['label' => __('apiplatform.module.navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
