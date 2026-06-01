<?php

declare(strict_types=1);

return [
    'description' => 'Public marketing landing page demo surface.',
    'routes' => [
        'web' => [
            '/landing',
        ],
        'api' => [
            '/api/public/landing',
        ],
        'aliases' => [],
        'prefixes' => [
            '/landing',
            '/api/public/landing',
        ],
    ],
    'navigation' => [
        'admin' => [],
        'public' => [
            [
                'label' => 'Landing',
                'href' => '/landing',
                'matches' => [
                    '/landing',
                ],
                'hint' => 'Marketing landing page demo surface.',
                'order' => 20,
            ],
        ],
        'breadcrumbs' => [],
    ],
];
