<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;

return [
    'description' => 'Authenticated notification APIs and websocket token issuance.',
    'routes' => [
        'web' => [],
        'api' => [
            '/api/ws-token',
            '/api/notifications',
            '/api/notifications/unread-count',
            '/api/notifications/read-all',
            '/api/notifications/{id}/read',
            '/api/presence/{resourceKey}/{recordId}/heartbeat',
        ],
        'aliases' => [],
        'prefixes' => [
            '/api/ws-token',
            '/api/notifications',
            '/api/presence',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => [
                '/api/ws-token',
                '/api/notifications',
                '/api/presence',
            ],
            'middleware_all' => [
                AuthMiddleware::class,
            ],
        ],
    ],
    'feature_flags' => [
        'websocket_enabled',
        'notifications',
    ],
];
