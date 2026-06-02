<?php

declare(strict_types=1);

return [
    'description' => __('audit.module.description'),
    'routes' => [
        'web' => [
            '/audit-log',
            '/audit-log/{id}',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/audit-log',
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-audit-log',
            'label' => __('audit.module.permission_label'),
            'description' => __('audit.module.permission_description'),
            'action' => 'manage',
            'resource' => 'audit-log',
            'role_fallback_any' => ['admin'],
            'abilities_any' => ['view-any', 'view', 'export'],
        ],
    ],
    'navigation' => [
        'admin' => [
            [
                'context' => 'operations',
                'label' => __('audit.module.navigation_label'),
                'href' => '/audit-log',
                'icon' => 'ti ti-history',
                'matches' => ['/audit-log'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('audit.module.navigation_hint'),
                'order' => 45,
                'visibility' => [
                    ['permissions_any' => ['manage-audit-log']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/audit-log/{id}',
                'trail' => [
                    ['label' => __('audit.module.navigation_label'), 'href' => '/audit-log'],
                    ['label' => __('audit.module.breadcrumb_show'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/audit-log',
                'trail' => [
                    ['label' => __('audit.module.navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
