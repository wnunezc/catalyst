<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;

return [
    'description' => __('automation.module.description'),
    'routes' => [
        'web' => [
            '/automation-rules',
            '/automation-rules/create',
            '/automation-rules/{id}',
            '/automation-rules/{id}/edit',
        ],
        'api' => [
            '/api/v1/automation-rules',
            '/api/v1/automation-rules/{id}',
            '/api/v1/automation-rules/{id}/run',
        ],
        'aliases' => [],
        'prefixes' => [
            '/automation-rules',
            '/api/v1/automation-rules',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/automation-rules'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/api/v1/automation-rules'],
            'middleware_all' => [
                ApiTokenMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-automation-rules',
            'label' => __('automation.module.permission_label'),
            'description' => __('automation.module.permission_description'),
            'action' => 'manage',
            'resource' => 'automation-rules',
            'abilities_any' => [
                'view-any',
                'view',
                'create',
                'update',
                'delete',
                'activate',
                'pause',
                'archive',
                'restore',
                'run',
            ],
            'role_fallback_any' => ['admin'],
        ],
    ],
    'navigation' => [
        'admin' => [
            [
                'context' => 'operations',
                'label' => __('automation.module.navigation_label'),
                'href' => '/automation-rules',
                'icon' => 'ti ti-bolt',
                'matches' => ['/automation-rules'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('automation.module.navigation_hint'),
                'order' => 65,
                'visibility' => [
                    ['permissions_any' => ['manage-automation-rules']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/automation-rules/create',
                'trail' => [
                    ['label' => __('automation.module.navigation_label'), 'href' => '/automation-rules'],
                    ['label' => __('automation.module.breadcrumb_create'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/automation-rules/{id}/edit',
                'trail' => [
                    ['label' => __('automation.module.navigation_label'), 'href' => '/automation-rules'],
                    ['label' => __('automation.module.breadcrumb_edit'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/automation-rules/{id}',
                'trail' => [
                    ['label' => __('automation.module.navigation_label'), 'href' => '/automation-rules'],
                    ['label' => __('automation.module.breadcrumb_show'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/automation-rules',
                'trail' => [
                    ['label' => __('automation.module.navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
