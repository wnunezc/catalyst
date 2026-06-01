<?php

declare(strict_types=1);

return [
    'description' => 'Authenticated frozen demo baseline surface for the INSPINIA UI reference work.',
    'routes' => [
        'web' => [
            '/demo-ui',
            '/demo-ui/basic-elements',
            '/demo-ui/pickers',
            '/demo-ui/select',
            '/demo-ui/validation',
            '/demo-ui/wizard',
            '/demo-ui/file-uploads',
            '/demo-ui/text-editors',
            '/demo-ui/range-slider',
            '/demo-ui/charts/{family}/{page}',
            '/demo-ui/tables/datatables/{page}',
            '/demo-ui/tables/{page}',
            '/demo-ui/accordions',
            '/demo-ui/alerts',
            '/demo-ui/badges',
            '/demo-ui/breadcrumb',
            '/demo-ui/buttons',
            '/demo-ui/cards',
            '/demo-ui/carousel',
            '/demo-ui/collapse',
            '/demo-ui/colors',
            '/demo-ui/dropdowns',
            '/demo-ui/grid-options',
            '/demo-ui/images',
            '/demo-ui/links',
            '/demo-ui/list-group',
            '/demo-ui/modals',
            '/demo-ui/notifications',
            '/demo-ui/offcanvas',
            '/demo-ui/pagination',
            '/demo-ui/placeholders',
            '/demo-ui/popovers',
            '/demo-ui/progress',
            '/demo-ui/scrollspy',
            '/demo-ui/spinners',
            '/demo-ui/tabs',
            '/demo-ui/tooltips',
            '/demo-ui/typography',
            '/demo-ui/utilities',
            '/demo-ui/videos',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/demo-ui',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => [
                '/demo-ui',
            ],
            'middleware_all' => [
                'Catalyst\\Framework\\Middleware\\AuthMiddleware',
            ],
        ],
    ],
    'navigation' => [
        'admin' => [],
        'public' => [],
        'breadcrumbs' => [],
    ],
];
