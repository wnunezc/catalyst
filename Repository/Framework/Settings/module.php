<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Middleware\SetupGuardMiddleware;

return [
    'description' => 'Framework setup and health panels.',
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
                'label' => 'Environment Setup',
                'href' => '/configuration/environment-setup',
                'icon' => 'ti ti-adjustments-cog',
                'matches' => ['/configuration/environment-setup'],
                'group' => 'configuration',
                'group_label' => 'Configuration',
                'group_order' => 10,
                'hint' => 'Bootstrap and runtime settings',
                'order' => 10,
            ],
            [
                'context' => 'configuration',
                'label' => 'Application Health',
                'href' => '/configuration/application-health',
                'icon' => 'ti ti-heartbeat',
                'matches' => ['/configuration/application-health'],
                'group' => 'configuration',
                'group_label' => 'Configuration',
                'group_order' => 10,
                'hint' => 'Health panel and probes',
                'order' => 20,
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/configuration/environment-setup',
                'trail' => [
                    ['label' => 'Configuration', 'href' => null],
                    ['label' => 'Environment Setup', 'href' => null],
                ],
            ],
            [
                'pattern' => '/configuration/application-health',
                'trail' => [
                    ['label' => 'Configuration', 'href' => '/configuration/environment-setup'],
                    ['label' => 'Application Health', 'href' => null],
                ],
            ],
        ],
    ],
];
