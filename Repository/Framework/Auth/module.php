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
    'description' => 'Authentication, recovery, MFA and social access surfaces.',
    'routes' => [
        'web' => [
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password/{token}',
            '/verify-email',
            '/verify-email/{token}',
            '/logout',
            '/auth/social/{provider}',
            '/auth/social/callback/{provider}',
            '/mfa/setup',
            '/mfa/challenge',
            '/mfa/enable',
            '/mfa/disable',
            '/mfa/verify',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/verify-email',
            '/logout',
            '/auth/social',
            '/mfa',
        ],
    ],
    'feature_flags' => [
        'social_auth',
        'mfa',
    ],
    'seeds' => [
        'users',
        'remember_tokens',
        'email_verification_tokens',
        'password_reset_tokens',
    ],
];
