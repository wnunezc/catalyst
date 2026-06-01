<?php

declare(strict_types=1);

return [
    'description' => 'Public store and catalog demo surface.',
    'routes' => [
        'web' => [
            '/store',
        ],
        'api' => [
            '/api/public/store',
        ],
        'aliases' => [],
        'prefixes' => [
            '/store',
            '/api/public/store',
        ],
    ],
    'navigation' => [
        'admin' => [],
        'public' => [
            [
                'label' => 'Store',
                'href' => '/store',
                'matches' => [
                    '/store',
                ],
                'hint' => 'Storefront and product catalog demo surface.',
                'order' => 30,
            ],
        ],
        'breadcrumbs' => [],
    ],
];
