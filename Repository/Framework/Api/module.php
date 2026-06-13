<?php

declare(strict_types=1);

return [
    'description' => 'Versioned transversal framework API.',
    'routes' => [
        'web' => [],
        'api' => [
            '/api/v1/catalog',
            '/api/v1/calendar/events',
            '/api/v1/workflows',
            '/api/v1/workflows/{id}/transition',
            '/api/v1/versions/{resourceKey}/{recordId}',
            '/api/v1/versions/{id}/restore',
        ],
        'aliases' => [],
        'prefixes' => [
            '/api/v1/catalog',
            '/api/v1/calendar',
            '/api/v1/workflows',
            '/api/v1/versions',
        ],
    ],
    'route_guards' => [[
        'patterns' => ['/api/v1/catalog', '/api/v1/calendar', '/api/v1/workflows', '/api/v1/versions'],
        'middleware_all' => [Catalyst\Framework\Middleware\ApiTokenMiddleware::class],
    ]],
    'permissions' => [],
    'permission_migrations' => [],
    'health_checks' => [],
    'feature_flags' => [],
    'navigation' => [
        'shell' => [],
        'public' => [],
        'application' => [],
        'breadcrumbs' => [],
    ],
];
