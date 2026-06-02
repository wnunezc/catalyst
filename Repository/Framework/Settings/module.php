<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Middleware\SetupGuardMiddleware;

return [
    'description' => __('settings.module.description'),
    'routes' => [
        'web' => [
            '/configuration/environment-setup',
            '/configuration/application-health',
            '/configuration/application-health/live',
            '/configuration/application-health/ready',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/configuration/environment-setup',
            '/configuration/application-health',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/configuration/environment-setup'],
            'middleware_all' => [SetupGuardMiddleware::class],
        ],
        [
            'patterns' => ['=/configuration/application-health'],
            'middleware_all' => [AuthMiddleware::class, RoleMiddleware::class],
        ],
    ],
    'permissions' => [],
    'navigation' => [
        'admin' => [
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
            ],
            [
                'context' => 'configuration',
                'label' => __('settings.module.health_label'),
                'href' => '/configuration/application-health',
                'icon' => 'ti ti-heartbeat',
                'matches' => ['/configuration/application-health'],
                'group' => 'configuration',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 10,
                'hint' => __('settings.module.health_hint'),
                'order' => 20,
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/configuration/environment-setup',
                'trail' => [
                    ['label' => __('settings.module.home_label'), 'href' => null],
                    ['label' => __('settings.settings.title'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/configuration/application-health',
                'trail' => [
                    ['label' => __('settings.module.home_label'), 'href' => '/configuration/environment-setup'],
                    ['label' => __('settings.module.health_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
