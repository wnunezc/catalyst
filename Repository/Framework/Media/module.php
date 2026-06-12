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
use Catalyst\Framework\Middleware\RoleMiddleware;

return [
    'description' => __('media.module.description'),
    'routes' => [
        'web' => [
            '/workspaces/media-library',
            '/workspaces/media-library/upload',
            '/workspaces/media-library/{id}/edit',
            '/workspaces/media-fields',
            '/workspaces/media-fields/create',
            '/workspaces/media-fields/{id}/edit',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/workspaces/media-library',
            '/workspaces/media-fields',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => [
                '/workspaces/media-library',
            ],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => [
                '/workspaces/media-fields',
            ],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-media-library',
            'label' => __('media.module.library_permission_label'),
            'description' => __('media.module.library_permission_description'),
            'action' => 'manage',
            'resource' => 'media-library',
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-media-metadata',
            'label' => __('media.module.metadata_permission_label'),
            'description' => __('media.module.metadata_permission_description'),
            'action' => 'manage',
            'resource' => 'metadata-fields',
            'role_fallback_any' => ['admin'],
        ],
    ],
    'navigation' => [
        'shell' => [
            [
                'context' => 'workspaces',
                'label' => __('media.module.library_navigation_label'),
                'href' => '/workspaces/media-library',
                'icon' => 'ti ti-photo',
                'matches' => ['/workspaces/media-library'],
                'group' => 'media-documents',
                'group_label' => __('ui.shell.group_content'),
                'group_order' => 20,
                'hint' => __('media.module.library_navigation_hint'),
                'order' => 50,
                'visibility' => [
                    ['permissions_any' => ['manage-media-library']],
                ],
            ],
            [
                'context' => 'workspaces',
                'label' => __('media.module.fields_navigation_label'),
                'href' => '/workspaces/media-fields',
                'icon' => 'ti ti-list-details',
                'matches' => ['/workspaces/media-fields'],
                'group' => 'media-documents',
                'group_label' => __('ui.shell.group_content'),
                'group_order' => 20,
                'hint' => __('media.module.fields_navigation_hint'),
                'order' => 55,
                'visibility' => [
                    ['permissions_any' => ['manage-media-metadata']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/workspaces/media-library/upload',
                'trail' => [
                    ['label' => __('media.module.library_navigation_label'), 'href' => '/workspaces/media-library'],
                    ['label' => __('media.module.breadcrumb_upload'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/media-library/{id}/edit',
                'trail' => [
                    ['label' => __('media.module.library_navigation_label'), 'href' => '/workspaces/media-library'],
                    ['label' => __('media.module.breadcrumb_edit_asset'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/media-library',
                'trail' => [
                    ['label' => __('media.module.library_navigation_label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/media-fields/create',
                'trail' => [
                    ['label' => __('media.module.fields_navigation_label'), 'href' => '/workspaces/media-fields'],
                    ['label' => __('media.module.breadcrumb_create_field'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/media-fields/{id}/edit',
                'trail' => [
                    ['label' => __('media.module.fields_navigation_label'), 'href' => '/workspaces/media-fields'],
                    ['label' => __('media.module.breadcrumb_edit_field'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/media-fields',
                'trail' => [
                    ['label' => __('media.module.fields_navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
