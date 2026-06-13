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
    'description' => 'Personal user account center, account security and assisted recovery flows.',
    'routes' => [
        'web' => [
            '/account/profile',
            '/account/security',
            '/account/security/mfa',
            '/account/recovery',
            '/account/recovery/mfa',
            '/account/recovery/support',
            '/account/recovery/compromised',
            '/account/activity',
            '/account-recovery/start',
            '/account-recovery/mfa',
            '/account-recovery/mfa/{token}',
            '/account-recovery/support',
            '/account-recovery/compromised',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/account',
            '/account-recovery',
        ],
    ],
    'route_guards' => [],
    'permissions' => [],
    'permission_migrations' => [],
    'health_checks' => [],
    'feature_flags' => [],
    'navigation' => [
        'shell' => [],
        'public' => [],
        'application' => [],
        'breadcrumbs' => [],
    ],
];
