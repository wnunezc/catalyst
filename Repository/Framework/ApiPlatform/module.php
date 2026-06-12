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

use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;

return [
    'description' => __('apiplatform.module.description'),
    'routes' => [
        'web' => [
            '/api-platform',
        ],
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
            '/api-platform',
            '/api/v1/catalog',
            '/api/v1/calendar',
            '/api/v1/workflows',
            '/api/v1/versions',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/api-platform'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/api/v1/catalog', '/api/v1/calendar', '/api/v1/workflows', '/api/v1/versions'],
            'middleware_all' => [
                ApiTokenMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-api-platform',
            'label' => __('apiplatform.module.permission_label'),
            'description' => __('apiplatform.module.permission_description'),
            'action' => 'manage',
            'resource' => 'api-platform',
            'abilities_any' => ['view-any', 'create', 'revoke'],
            'role_fallback_any' => ['admin'],
        ],
    ],
    'navigation' => [
        'shell' => [
            [
                'context' => 'operations',
                'label' => __('apiplatform.module.navigation_label'),
                'href' => '/api-platform',
                'icon' => 'ti ti-api',
                'matches' => ['/api-platform'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('apiplatform.module.navigation_hint'),
                'order' => 70,
                'visibility' => [
                    ['permissions_any' => ['manage-api-platform']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/api-platform',
                'trail' => [
                    ['label' => __('apiplatform.module.navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
