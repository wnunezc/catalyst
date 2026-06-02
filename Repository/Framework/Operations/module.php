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

return [
    'description' => 'operations.module.description',
    'routes' => [
        'web' => [
            '/operations',
            '/configuration/platform-appearance',
            '/workspaces/locale-tools',
            '/workspaces/module-designer',
            '/configuration/feature-flags',
            '/configuration/plugins',
            '/operations/deployments',
            '/operations/tenancy',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/operations',
            '/configuration',
            '/workspaces/module-designer',
            '/workspaces/locale-tools',
        ],
    ],
    'settings' => [],
    'permissions' => [
        [
            'slug' => 'manage-platform-operations',
            'label' => 'operations.module.permission_label',
            'description' => 'operations.module.permission_description',
            'action' => 'manage',
            'resource' => 'operations',
            'role_fallback_any' => [
                'admin',
            ],
        ],
    ],
    'health_checks' => [],
    'feature_flags' => [
        'module.framework.operations',
    ],
    'route_guards' => [
        [
            'patterns' => [
                '/operations',
                        '/configuration',
                        '/workspaces/module-designer',
                        '/workspaces/locale-tools',
                    ],
                    'middleware_all' => [
                'Catalyst\\Framework\\Middleware\\AuthMiddleware',
                'Catalyst\\Framework\\Middleware\\RoleMiddleware',
            ],
        ],
    ],
    'navigation' => [
        'admin' => [
            [
                'context' => 'configuration',
                'label' => 'operations.title',
                'href' => '/operations',
                'icon' => 'ti ti-package',
                'matches' => [
                    '/operations',
                    '/configuration/platform-appearance',
                    '/workspaces/locale-tools',
                    '/workspaces/module-designer',
                    '/configuration/feature-flags',
                    '/configuration/plugins',
                    '/operations/deployments',
                    '/operations/tenancy',
                ],
                'group' => 'operations',
                'group_label' => 'ui.shell.group_operations',
                'group_order' => 40,
                'hint' => 'operations.module.navigation_hint',
                'order' => 50,
                'children' => [
                    [
                        'label' => 'operations.appearance.page_title',
                        'href' => '/configuration/platform-appearance',
                        'icon' => 'ti ti-palette',
                        'matches' => ['/configuration/platform-appearance'],
                    ],
                    [
                        'label' => 'operations.localization.page_title',
                        'href' => '/workspaces/locale-tools',
                        'icon' => 'ti ti-language',
                        'matches' => ['/workspaces/locale-tools'],
                    ],
                    [
                        'label' => 'operations.module_designer.page_title',
                        'href' => '/workspaces/module-designer',
                        'icon' => 'ti ti-template',
                        'matches' => ['/workspaces/module-designer'],
                    ],
                    [
                        'label' => 'operations.feature_flags.title',
                        'href' => '/configuration/feature-flags',
                        'icon' => 'ti ti-flag-3',
                        'matches' => ['/configuration/feature-flags'],
                    ],
                    [
                        'label' => 'operations.plugins.title',
                        'href' => '/configuration/plugins',
                        'icon' => 'ti ti-plug-connected',
                        'matches' => ['/configuration/plugins'],
                    ],
                    [
                        'label' => 'operations.deployments.title',
                        'href' => '/operations/deployments',
                        'icon' => 'ti ti-rocket',
                        'matches' => ['/operations/deployments'],
                    ],
                    [
                        'label' => 'operations.tenancy.title',
                        'href' => '/operations/tenancy',
                        'icon' => 'ti ti-building-community',
                        'matches' => ['/operations/tenancy'],
                    ],
                ],
                'visibility' => [
                    [
                        'permissions_any' => [
                            'manage-platform-operations',
                        ],
                    ],
                ],
            ],
        ],
        'public' => [],
        'breadcrumbs' => [
            [
                'pattern' => '/operations',
                'trail' => [
                    [
                        'label' => 'operations.title',
                        'href' => NULL,
                    ],
                ],
            ],
            [
                'pattern' => '/configuration/platform-appearance',
                'trail' => [
                    [
                        'label' => 'operations.title',
                        'href' => '/operations',
                    ],
                    [
                        'label' => 'operations.appearance.page_title',
                        'href' => NULL,
                    ],
                ],
            ],
            [
                'pattern' => '/workspaces/locale-tools',
                'trail' => [
                    [
                        'label' => 'operations.title',
                        'href' => '/operations',
                    ],
                    [
                        'label' => 'operations.localization.page_title',
                        'href' => NULL,
                    ],
                ],
            ],
            [
                'pattern' => '/workspaces/module-designer',
                'trail' => [
                    [
                        'label' => 'operations.title',
                        'href' => '/operations',
                    ],
                    [
                        'label' => 'operations.module_designer.page_title',
                        'href' => NULL,
                    ],
                ],
            ],
            [
                'pattern' => '/configuration/feature-flags',
                'trail' => [
                    [
                        'label' => 'operations.title',
                        'href' => '/operations',
                    ],
                    [
                        'label' => 'operations.feature_flags.title',
                        'href' => NULL,
                    ],
                ],
            ],
            [
                'pattern' => '/configuration/plugins',
                'trail' => [
                    [
                        'label' => 'operations.title',
                        'href' => '/operations',
                    ],
                    [
                        'label' => 'operations.plugins.title',
                        'href' => NULL,
                    ],
                ],
            ],

            [
                'pattern' => '/operations/deployments',
                'trail' => [
                    [
                        'label' => 'operations.title',
                        'href' => '/operations',
                    ],
                    [
                        'label' => 'operations.deployments.title',
                        'href' => NULL,
                    ],
                ],
            ],
            [
                'pattern' => '/operations/tenancy',
                'trail' => [
                    [
                        'label' => 'operations.title',
                        'href' => '/operations',
                    ],
                    [
                        'label' => 'operations.tenancy.title',
                        'href' => NULL,
                    ],
                ],
            ],
        ],
    ],
];
