<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * @package Catalyst
 */

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Middleware\SetupGuardMiddleware;

return [
    'description' => __('settings.module.description'),
    'routes' => [
        'web' => [
            '/configuration/environment-setup',
            '/configuration/platform-appearance',
            '/configuration/feature-flags',
            '/configuration/plugins',
            '/configuration/application-health',
            '/configuration/application-health/live',
            '/configuration/application-health/ready',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/configuration/environment-setup',
            '/configuration/platform-appearance',
            '/configuration/feature-flags',
            '/configuration/plugins',
            '/configuration/application-health',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/configuration/environment-setup'],
            'middleware_all' => [SetupGuardMiddleware::class],
        ],
        [
            'patterns' => ['=/configuration/platform-appearance'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/configuration/feature-flags'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/configuration/plugins'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['=/configuration/application-health'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-platform-configuration',
            'label' => 'Manage platform configuration',
            'description' => 'Manage protected platform configuration surfaces.',
            'action' => 'manage',
            'resource' => 'configuration',
            'role_fallback_any' => ['admin'],
        ],
    ],
    'health_checks' => [],
    'feature_flags' => [],
    'navigation' => [
        'shell' => [
            [
                'context' => 'configuration',
                'label' => __('settings.appearance.page_title'),
                'href' => '/configuration/platform-appearance',
                'icon' => 'ti ti-palette',
                'matches' => ['/configuration/platform-appearance'],
                'group' => 'configuration',
                'group_label' => __('settings.module.configuration_label'),
                'group_order' => 10,
                'hint' => __('settings.appearance.navigation_hint'),
                'order' => 15,
                'visibility' => [
                    ['permissions_any' => ['manage-platform-configuration']],
                ],
            ],
            [
                'context' => 'configuration',
                'label' => __('settings.feature_flags.title'),
                'href' => '/configuration/feature-flags',
                'icon' => 'ti ti-flag-3',
                'matches' => ['/configuration/feature-flags'],
                'group' => 'configuration',
                'group_label' => __('settings.module.configuration_label'),
                'group_order' => 10,
                'hint' => __('settings.feature_flags.navigation_hint'),
                'order' => 18,
                'visibility' => [
                    ['permissions_any' => ['manage-platform-configuration']],
                ],
            ],
            [
                'context' => 'configuration',
                'label' => __('settings.plugins.title'),
                'href' => '/configuration/plugins',
                'icon' => 'ti ti-plug-connected',
                'matches' => ['/configuration/plugins'],
                'group' => 'configuration',
                'group_label' => __('settings.module.configuration_label'),
                'group_order' => 10,
                'hint' => __('settings.plugins.navigation_hint'),
                'order' => 19,
                'visibility' => [
                    ['permissions_any' => ['manage-platform-configuration']],
                ],
            ],
            [
                'context' => 'configuration',
                'label' => __('settings.module.setup_label'),
                'href' => '/configuration/environment-setup',
                'icon' => 'ti ti-adjustments-cog',
                'matches' => ['/configuration/environment-setup'],
                'group' => 'configuration',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 10,
                'hint' => __('settings.module.setup_hint'),
                'order' => 10,
                'visibility' => [
                    ['roles_any' => ['admin']],
                ],
            ],
            [
                'context' => 'configuration',
                'label' => __('settings.module.health_label'),
                'href' => '/configuration/application-health',
                'icon' => 'ti ti-heartbeat',
                'matches' => ['/configuration/application-health'],
                'group' => 'configuration',
                'group_label' => __('settings.module.configuration_label'),
                'group_order' => 10,
                'hint' => __('settings.module.health_hint'),
                'order' => 20,
                'visibility' => [
                    ['permissions_any' => ['manage-platform-configuration']],
                ],
            ],
        ],
        'public' => [],
        'application' => [],
        'breadcrumbs' => [
            [
                'pattern' => '/configuration/environment-setup',
                'trail' => [
                    ['label' => __('settings.module.home_label'), 'href' => null],
                    ['label' => __('settings.settings.title'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/configuration/platform-appearance',
                'trail' => [
                    ['label' => __('settings.module.configuration_label'), 'href' => null],
                    ['label' => __('settings.appearance.page_title'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/configuration/feature-flags',
                'trail' => [
                    ['label' => __('settings.module.configuration_label'), 'href' => null],
                    ['label' => __('settings.feature_flags.title'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/configuration/plugins',
                'trail' => [
                    ['label' => __('settings.module.configuration_label'), 'href' => null],
                    ['label' => __('settings.plugins.title'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/configuration/application-health',
                'trail' => [
                    ['label' => __('settings.module.configuration_label'), 'href' => null],
                    ['label' => __('settings.module.health_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
