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
    'description' => 'Public frozen demo baseline surface for the INSPINIA UI reference work.',
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
    'route_guards' => [],
    'navigation' => [
        'shell' => [
            [
                'context' => 'devtools',
                'label' => 'Demo UI',
                'href' => '/demo-ui',
                'icon' => 'ti ti-components',
                'matches' => ['/demo-ui*'],
                'group' => 'devtools',
                'group_label' => 'Devtools',
                'group_order' => 90,
                'hint' => 'Frozen Inspinia reference surface.',
                'order' => 40,
                'visibility' => [
                    ['roles_any' => ['admin'], 'environments' => ['development']],
                ],
            ],
        ],
        'public' => [],
        'breadcrumbs' => [],
    ],
];
