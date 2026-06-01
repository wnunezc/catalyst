<?php

declare(strict_types=1);

return [
    'description' => 'Personal account dashboard surface. Guests see a safe login/register gateway; authenticated users see the Account Shell.',
    'routes' => [
        'web' => [
            '/dashboard',
        ],
        'api' => [
            '/api/public/dashboard',
        ],
        'aliases' => [],
        'prefixes' => [
            '/dashboard',
            '/api/public/dashboard',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => [
                '/api/public/dashboard',
            ],
            'middleware_all' => [
                Catalyst\Framework\Middleware\AuthMiddleware::class,
            ],
        ],
    ],
    'navigation' => [
        'admin' => [],
        'public' => [
            [
                'label' => 'Dashboard',
                'href' => '/dashboard',
                'matches' => [
                    '/dashboard',
                ],
                'hint' => 'Personal account dashboard surface.',
                'order' => 40,
                'visibility' => [],
            ],
        ],
        'breadcrumbs' => [],
    ],
];
