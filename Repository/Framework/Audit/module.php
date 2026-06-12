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

return [
    'description' => __('audit.module.description'),
    'routes' => [
        'web' => [
            '/audit-log',
            '/audit-log/{id}',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/audit-log',
        ],
    ],
    'permissions' => [
        [
            'slug' => 'manage-audit-log',
            'label' => __('audit.module.permission_label'),
            'description' => __('audit.module.permission_description'),
            'action' => 'manage',
            'resource' => 'audit-log',
            'role_fallback_any' => ['admin'],
            'abilities_any' => ['view-any', 'view', 'export'],
        ],
    ],
    'navigation' => [
        'shell' => [
            [
                'context' => 'operations',
                'label' => __('audit.module.navigation_label'),
                'href' => '/audit-log',
                'icon' => 'ti ti-history',
                'matches' => ['/audit-log'],
                'group' => 'platform-tools',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 30,
                'hint' => __('audit.module.navigation_hint'),
                'order' => 45,
                'visibility' => [
                    ['permissions_any' => ['manage-audit-log']],
                ],
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/audit-log/{id}',
                'trail' => [
                    ['label' => __('audit.module.navigation_label'), 'href' => '/audit-log'],
                    ['label' => __('audit.module.breadcrumb_show'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/audit-log',
                'trail' => [
                    ['label' => __('audit.module.navigation_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
