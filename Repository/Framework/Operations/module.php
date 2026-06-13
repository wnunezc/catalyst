<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * @package Catalyst
 */

return [
    'description' => __('operations.module.description'),
    'routes' => [
        'web' => [
            '/operations/audit-log',
            '/operations/audit-log/{id}',
            '/operations/api-management',
            '/operations/automation-rules',
            '/operations/automation-rules/create',
            '/operations/automation-rules/{id}',
            '/operations/automation-rules/{id}/edit',
            '/operations/deployments',
            '/operations/tenancy',
        ],
        'api' => [
            '/api/v1/automation-rules',
            '/api/v1/automation-rules/{id}',
            '/api/v1/automation-rules/{id}/run',
        ],
        'aliases' => [],
        'prefixes' => [
            '/operations/audit-log',
            '/operations/api-management',
            '/operations/automation-rules',
            '/operations/deployments',
            '/operations/tenancy',
            '/api/v1/automation-rules',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/operations/tenancy'],
            'middleware_all' => [
                Catalyst\Framework\Middleware\AuthMiddleware::class,
                Catalyst\Framework\Middleware\RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/operations/deployments'],
            'middleware_all' => [
                Catalyst\Framework\Middleware\AuthMiddleware::class,
                Catalyst\Framework\Middleware\RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/operations/api-management'],
            'middleware_all' => [
                Catalyst\Framework\Middleware\AuthMiddleware::class,
                Catalyst\Framework\Middleware\RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/operations/automation-rules'],
            'middleware_all' => [
                Catalyst\Framework\Middleware\AuthMiddleware::class,
                Catalyst\Framework\Middleware\RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/api/v1/automation-rules'],
            'middleware_all' => [
                Catalyst\Framework\Middleware\ApiTokenMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/operations/audit-log'],
            'middleware_all' => [
                Catalyst\Framework\Middleware\AuthMiddleware::class,
                Catalyst\Framework\Middleware\RoleMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-operations-deployments',
            'label' => __('operations.permissions.deployments.label'),
            'description' => __('operations.permissions.deployments.description'),
            'action' => 'manage',
            'resource' => 'operations-deployments',
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-operations-tenancy',
            'label' => __('operations.permissions.tenancy.label'),
            'description' => __('operations.permissions.tenancy.description'),
            'action' => 'manage',
            'resource' => 'operations-tenancy',
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-operations-audit-log',
            'label' => __('operations.permissions.audit_log.label'),
            'description' => __('operations.permissions.audit_log.description'),
            'action' => 'manage',
            'resource' => 'operations-audit-log',
            'abilities_any' => ['view-any', 'view', 'export'],
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-operations-api-management',
            'label' => __('operations.permissions.api_management.label'),
            'description' => __('operations.permissions.api_management.description'),
            'action' => 'manage',
            'resource' => 'operations-api-management',
            'abilities_any' => ['view-any', 'create', 'revoke'],
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-operations-automation-rules',
            'label' => __('operations.permissions.automation_rules.label'),
            'description' => __('operations.permissions.automation_rules.description'),
            'action' => 'manage',
            'resource' => 'operations-automation-rules',
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
    'permission_migrations' => [],
    'health_checks' => [],
    'feature_flags' => [],
    'navigation' => [
        'shell' => [
            [
                'context' => 'operations',
                'label' => __('operations.tenancy.navigation_label'),
                'href' => '/operations/tenancy',
                'icon' => 'ti ti-building-community',
                'matches' => ['/operations/tenancy'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('operations.tenancy.navigation_hint'),
                'order' => 30,
                'visibility' => [
                    ['permissions_any' => ['manage-operations-tenancy']],
                ],
            ],
            [
                'context' => 'operations',
                'label' => __('operations.deployments.navigation_label'),
                'href' => '/operations/deployments',
                'icon' => 'ti ti-rocket',
                'matches' => ['/operations/deployments'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('operations.deployments.navigation_hint'),
                'order' => 20,
                'visibility' => [
                    ['permissions_any' => ['manage-operations-deployments']],
                ],
            ],
            [
                'context' => 'operations',
                'label' => __('automation.module.navigation_label'),
                'href' => '/operations/automation-rules',
                'icon' => 'ti ti-bolt',
                'matches' => ['/operations/automation-rules'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('automation.module.navigation_hint'),
                'order' => 65,
                'visibility' => [
                    ['permissions_any' => ['manage-operations-automation-rules']],
                ],
            ],
            [
                'context' => 'operations',
                'label' => __('apimanagement.module.navigation_label'),
                'href' => '/operations/api-management',
                'icon' => 'ti ti-api',
                'matches' => ['/operations/api-management'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('apimanagement.module.navigation_hint'),
                'order' => 70,
                'visibility' => [
                    ['permissions_any' => ['manage-operations-api-management']],
                ],
            ],
            [
                'context' => 'operations',
                'label' => __('audit.module.navigation_label'),
                'href' => '/operations/audit-log',
                'icon' => 'ti ti-history',
                'matches' => ['/operations/audit-log'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('audit.module.navigation_hint'),
                'order' => 45,
                'visibility' => [
                    ['permissions_any' => ['manage-operations-audit-log']],
                ],
            ],
        ],
        'public' => [],
        'application' => [],
        'breadcrumbs' => [
            [
                'pattern' => '/operations/tenancy',
                'trail' => [
                    ['label' => __('operations.tenancy.navigation_label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/operations/deployments',
                'trail' => [
                    ['label' => __('operations.deployments.navigation_label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/operations/automation-rules/create',
                'trail' => [
                    ['label' => __('automation.module.navigation_label'), 'href' => '/operations/automation-rules'],
                    ['label' => __('automation.module.breadcrumb_create'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/operations/automation-rules/{id}/edit',
                'trail' => [
                    ['label' => __('automation.module.navigation_label'), 'href' => '/operations/automation-rules'],
                    ['label' => __('automation.module.breadcrumb_edit'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/operations/automation-rules/{id}',
                'trail' => [
                    ['label' => __('automation.module.navigation_label'), 'href' => '/operations/automation-rules'],
                    ['label' => __('automation.module.breadcrumb_show'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/operations/automation-rules',
                'trail' => [
                    ['label' => __('automation.module.navigation_label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/operations/api-management',
                'trail' => [
                    ['label' => __('apimanagement.module.navigation_label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/operations/audit-log/{id}',
                'trail' => [
                    ['label' => __('audit.module.navigation_label'), 'href' => '/operations/audit-log'],
                    ['label' => __('audit.module.breadcrumb_show'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/operations/audit-log',
                'trail' => [
                    ['label' => __('audit.module.navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
