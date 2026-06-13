<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;

/**
 * Catalyst PHP Framework
 *
 * @package Catalyst
 */

return [
    'description' => __('workspaces.module.description'),
    'routes' => [
        'web' => [
            '/workspaces/catalogs',
            '/workspaces/catalogs/create',
            '/workspaces/catalogs/{id}',
            '/workspaces/catalogs/{id}/edit',
            '/workspaces/catalogs/{id}/items/create',
            '/workspaces/catalogs/{id}/items/{itemId}/edit',
            '/workspaces/media-fields',
            '/workspaces/media-fields/create',
            '/workspaces/media-fields/{id}/edit',
            '/workspaces/media-library',
            '/workspaces/media-library/upload',
            '/workspaces/media-library/{id}/edit',
            '/workspaces/document-templates',
            '/workspaces/document-templates/create',
            '/workspaces/document-templates/{id}',
            '/workspaces/document-templates/{id}/edit',
            '/workspaces/module-designer',
            '/workspaces/locale-tools',
        ],
        'api' => [
            '/api/v1/document-templates',
            '/api/v1/document-templates/{id}',
            '/api/v1/document-templates/{id}/preview',
            '/api/v1/document-templates/{id}/export',
        ],
        'aliases' => [],
        'prefixes' => [
            '/workspaces/catalogs',
            '/workspaces/media-fields',
            '/workspaces/media-library',
            '/workspaces/document-templates',
            '/workspaces/module-designer',
            '/workspaces/locale-tools',
            '/api/v1/document-templates',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/workspaces/locale-tools'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/workspaces/module-designer'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/workspaces/catalogs'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/workspaces/media-fields'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
        [
            'patterns' => ['/workspaces/media-library'],
            'middleware_all' => [
                AuthMiddleware::class,
                RoleMiddleware::class,
            ],
        ],
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
            'slug' => 'manage-workspaces-catalogs',
            'label' => __('workspaces.permissions.catalogs.label'),
            'description' => __('workspaces.permissions.catalogs.description'),
            'action' => 'manage',
            'resource' => 'workspaces-catalogs',
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-workspaces-module-designer',
            'label' => __('workspaces.permissions.module_designer.label'),
            'description' => __('workspaces.permissions.module_designer.description'),
            'action' => 'manage',
            'resource' => 'workspaces-module-designer',
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-workspaces-media-fields',
            'label' => __('workspaces.permissions.media_fields.label'),
            'description' => __('workspaces.permissions.media_fields.description'),
            'action' => 'manage',
            'resource' => 'workspaces-media-fields',
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-workspaces-media-library',
            'label' => __('workspaces.permissions.media_library.label'),
            'description' => __('workspaces.permissions.media_library.description'),
            'action' => 'manage',
            'resource' => 'workspaces-media-library',
            'role_fallback_any' => ['admin'],
        ],
        [
            'slug' => 'manage-workspaces-document-templates',
            'label' => __('workspaces.permissions.document_templates.label'),
            'description' => __('workspaces.permissions.document_templates.description'),
            'action' => 'manage',
            'resource' => 'workspaces-document-templates',
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
        [
            'slug' => 'manage-workspaces-localization',
            'label' => __('workspaces.permissions.localization.label'),
            'description' => __('workspaces.permissions.localization.description'),
            'action' => 'manage',
            'resource' => 'workspaces-localization',
            'role_fallback_any' => ['admin'],
        ],
    ],
    'permission_migrations' => [],
    'health_checks' => [],
    'feature_flags' => [],
    'navigation' => [
        'shell' => [
            [
                'context' => 'workspaces',
                'label' => __('workspaces.localization.navigation.label'),
                'href' => '/workspaces/locale-tools',
                'icon' => 'ti ti-language',
                'matches' => ['/workspaces/locale-tools'],
                'group' => 'module-tools',
                'group_label' => __('workspaces.module_designer.navigation.group'),
                'group_order' => 10,
                'hint' => __('workspaces.localization.navigation.hint'),
                'order' => 20,
                'visibility' => [
                    ['permissions_any' => ['manage-workspaces-localization']],
                ],
            ],
            [
                'context' => 'workspaces',
                'label' => __('workspaces.module_designer.navigation.label'),
                'href' => '/workspaces/module-designer',
                'icon' => 'ti ti-template',
                'matches' => ['/workspaces/module-designer'],
                'group' => 'module-tools',
                'group_label' => __('workspaces.module_designer.navigation.group'),
                'group_order' => 10,
                'hint' => __('workspaces.module_designer.navigation.hint'),
                'order' => 10,
                'visibility' => [
                    ['permissions_any' => ['manage-workspaces-module-designer']],
                ],
            ],
            [
                'context' => 'workspaces',
                'label' => __('catalogs.module.navigation_label'),
                'href' => '/workspaces/catalogs',
                'icon' => 'ti ti-books',
                'matches' => ['/workspaces/catalogs'],
                'group' => 'catalogs',
                'group_label' => 'Catalogs',
                'group_order' => 20,
                'hint' => __('catalogs.module.navigation_hint'),
                'order' => 58,
                'visibility' => [
                    ['permissions_any' => ['manage-workspaces-catalogs']],
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
                    ['permissions_any' => ['manage-workspaces-media-fields']],
                ],
            ],
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
                    ['permissions_any' => ['manage-workspaces-media-library']],
                ],
            ],
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
                    ['permissions_any' => ['manage-workspaces-document-templates']],
                ],
            ],
        ],
        'public' => [],
        'application' => [],
        'breadcrumbs' => [
            [
                'pattern' => '/workspaces/locale-tools',
                'trail' => [
                    ['label' => __('workspaces.localization.navigation.label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/module-designer',
                'trail' => [
                    ['label' => __('workspaces.module_designer.navigation.label'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/create',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_create'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/{id}/edit',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_edit'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/{id}/items/create',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_create_item'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/{id}/items/{itemId}/edit',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_edit_item'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs/{id}',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => '/workspaces/catalogs'],
                    ['label' => __('catalogs.module.breadcrumb_show'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/workspaces/catalogs',
                'trail' => [
                    ['label' => __('catalogs.module.navigation_label'), 'href' => null],
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
