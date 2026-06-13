<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

use Catalyst\Framework\Middleware\AuthMiddleware;

return [
    'description' => 'Authenticated notification APIs and websocket token issuance.',
    'routes' => [
        'web' => [],
        'api' => [
            '/runtime/websocket/token',
            '/runtime/notifications',
            '/runtime/notifications/unread-count',
            '/runtime/notifications/read-all',
            '/runtime/notifications/{id}/read',
            '/runtime/presence/{resourceKey}/{recordId}/heartbeat',
        ],
        'aliases' => [],
        'prefixes' => [
            '/runtime/websocket',
            '/runtime/notifications',
            '/runtime/presence',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => [
                '/runtime/websocket',
                '/runtime/notifications',
                '/runtime/presence',
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
