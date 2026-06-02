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
use Catalyst\Framework\Middleware\SetupGuardMiddleware;

return [
    'description' => __('settings.module.description'),
    'routes' => [
        'web' => [
            '/configuration/environment-setup',
            '/configuration/application-health',
            '/configuration/application-health/live',
            '/configuration/application-health/ready',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/configuration/environment-setup',
            '/configuration/application-health',
        ],
    ],
    'route_guards' => [
        [
            'patterns' => ['/configuration/environment-setup'],
            'middleware_all' => [SetupGuardMiddleware::class],
        ],
        [
            'patterns' => ['=/configuration/application-health'],
            'middleware_all' => [AuthMiddleware::class, RoleMiddleware::class],
        ],
    ],
    'permissions' => [],
    'navigation' => [
        'admin' => [
            [
                'context' => 'configuration',
                'label' => __('settings.module.setup_label'),
                'href' => '/configuration/environment-setup',
                'icon' => 'ti ti-adjustments-cog',
                'matches' => ['/configuration/environment-setup'],
                'group' => 'configuration',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 10,
                'hint' => __('settings.module.setup_hint'),
                'order' => 10,
            ],
            [
                'context' => 'configuration',
                'label' => __('settings.module.health_label'),
                'href' => '/configuration/application-health',
                'icon' => 'ti ti-heartbeat',
                'matches' => ['/configuration/application-health'],
                'group' => 'configuration',
                'group_label' => __('ui.shell.group_platform'),
                'group_order' => 10,
                'hint' => __('settings.module.health_hint'),
                'order' => 20,
            ],
        ],
        'breadcrumbs' => [
            [
                'pattern' => '/configuration/environment-setup',
                'trail' => [
                    ['label' => __('settings.module.home_label'), 'href' => null],
                    ['label' => __('settings.settings.title'), 'href' => null],
                ],
            ],
            [
                'pattern' => '/configuration/application-health',
                'trail' => [
                    ['label' => __('settings.module.home_label'), 'href' => '/configuration/environment-setup'],
                    ['label' => __('settings.module.health_label'), 'href' => null],
                ],
            ],
        ],
    ],
];
