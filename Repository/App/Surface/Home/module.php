<?php

declare(strict_types=1);

return [
    'description' => 'Public application home demo surface and canonical root entry.',
    'routes' => [
        'web' => [
            '/',
            '/home',
        ],
        'api' => [
            '/api/public/home',
        ],
        'aliases' => [],
        'prefixes' => [
            '/',
            '/home',
            '/api/public/home',
        ],
    ],
    'navigation' => [
        'admin' => [],
        'public' => [
            [
                'label' => 'Home',
                'href' => '/',
                'matches' => [
                    '/',
                    '/home',
                ],
                'hint' => 'Public home demo surface.',
                'order' => 10,
            ],
        ],
        'breadcrumbs' => [],
    ],
];
