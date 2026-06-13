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
    'description' => 'Personal account dashboard surface. Guests see a safe login/register gateway; authenticated users see the Account Shell.',
    'routes' => [
        'web' => [
            '/dashboard',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/dashboard',
        ],
    ],
    'route_guards' => [],
    'navigation' => [
        'shell' => [],
        'application' => [
            [
                'kind' => 'title',
                'label' => __('account.nav.dashboard'),
                'order' => 10,
                'visibility' => [
                    ['authenticated' => true],
                ],
            ],
            [
                'kind' => 'link',
                'label' => __('account.nav.dashboard'),
                'href' => '/dashboard',
                'icon' => 'ti ti-layout-dashboard',
                'hint' => __('account.nav_hints.dashboard'),
                'matches' => ['/dashboard'],
                'order' => 20,
                'visibility' => [
                    ['authenticated' => true],
                ],
            ],
        ],
        'public' => [
            [
                'label' => 'Dashboard',
                'href' => '/dashboard',
                'matches' => [
                    '/dashboard',
                ],
                'hint' => 'Personal account dashboard surface.',
                'order' => 40,
                'visibility' => [],
            ],
        ],
        'breadcrumbs' => [],
    ],
];
