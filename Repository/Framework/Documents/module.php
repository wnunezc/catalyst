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
    'description' => __('documents.module.description'),
    'routes' => [
        'web' => [
            '/workspaces/document-templates',
            '/workspaces/document-templates/create',
            '/workspaces/document-templates/{id}',
            '/workspaces/document-templates/{id}/edit',
        ],
        'api' => [
            '/api/v1/document-templates',
            '/api/v1/document-templates/{id}',
            '/api/v1/document-templates/{id}/preview',
            '/api/v1/document-templates/{id}/export',
        ],
        'aliases' => [],
        'prefixes' => [
            '/workspaces/document-templates',
            '/api/v1/document-templates',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/workspaces/document-templates'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-document-templates',
            'label' => __('documents.module.permission_label'),
            'description' => __('documents.module.permission_description'),
            'action' => 'manage',
            'resource' => 'document-templates',
            'abilities_any' => [
                'view-any',
                'view',
                'create',
                'update',
                'delete',
                'export',
                'restore',
                'submit-review',
                'approve',
                'reject',
                'archive',
            ],
            'role_fallback_any' => ['admin'],
        ],
    ],
    'navigation' => [
        'admin' => [
            [
                'context' => 'workspaces',
                'label' => __('documents.module.navigation_label'),
                'href' => '/workspaces/document-templates',
                'icon' => 'ti ti-file-description',
                'matches' => ['/workspaces/document-templates'],
                'group' => 'media-documents',
                'group_label' => __('ui.shell.group_content'),
                'group_order' => 20,
                'hint' => __('documents.module.navigation_hint'),
                'order' => 60,
                'visibility' => [
                    ['permissions_any' => ['manage-document-templates']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/workspaces/document-templates/create',
                'trail' => [
                    ['label' => __('documents.module.navigation_label'), 'href' => '/workspaces/document-templates'],
                    ['label' => __('documents.module.breadcrumb_create'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/document-templates/{id}/edit',
                'trail' => [
                    ['label' => __('documents.module.navigation_label'), 'href' => '/workspaces/document-templates'],
                    ['label' => __('documents.module.breadcrumb_edit'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/document-templates/{id}',
                'trail' => [
                    ['label' => __('documents.module.navigation_label'), 'href' => '/workspaces/document-templates'],
                    ['label' => __('documents.module.breadcrumb_show'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/document-templates',
                'trail' => [
                    ['label' => __('documents.module.navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
