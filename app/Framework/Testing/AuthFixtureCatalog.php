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

namespace Catalyst\Framework\Testing;

/**
 * Declares reusable authentication fixture profiles for development.
 *
 * @package Catalyst\Framework\Testing
 * Responsibility: Supplies fixture users, roles and permissions by profile key.
 */
final class AuthFixtureCatalog
{
    public const string DEFAULT_PROFILE = 'development';

    /**
     * Returns all configured fixture profiles.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function profiles(): array
    {
        return [
            self::DEFAULT_PROFILE => [
                'description' => 'Baseline auth/RBAC fixture profile for development runtime, QA users and reversible smoke flows.',
                'roles' => [
                    [
                        'id' => 1,
                        'name' => 'Administrator',
                        'slug' => 'admin',
                        'description' => 'Full system access',
                        'created_at' => '2026-05-15 18:40:27',
                    ],
                    [
                        'id' => 2,
                        'name' => 'User',
                        'slug' => 'user',
                        'description' => 'Standard user access',
                        'created_at' => '2026-05-15 18:40:27',
                    ],
                ],
                'permissions' => [
                    [
                        'id' => 1,
                        'name' => 'Manage Users',
                        'slug' => 'manage-users',
                        'description' => 'Create, edit, deactivate users',
                        'created_at' => '2026-05-15 18:40:27',
                    ],
                    [
                        'id' => 2,
                        'name' => 'View Dashboard',
                        'slug' => 'view-dashboard',
                        'description' => 'Access privileged dashboard',
                        'created_at' => '2026-05-15 18:40:27',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Manage Roles',
                        'slug' => 'manage-roles',
                        'description' => 'Create, edit, delete roles and permissions',
                        'created_at' => '2026-05-15 18:40:27',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Access DevTools',
                        'slug' => 'access-devtools',
                        'description' => 'Access development-only runtime tooling',
                        'created_at' => '2026-05-15 18:40:27',
                    ],
                ],
                'role_permissions' => [
                    ['role_slug' => 'admin', 'permission_slug' => 'manage-users'],
                    ['role_slug' => 'admin', 'permission_slug' => 'view-dashboard'],
                    ['role_slug' => 'admin', 'permission_slug' => 'manage-roles'],
                    ['role_slug' => 'admin', 'permission_slug' => 'access-devtools'],
                    ['role_slug' => 'user', 'permission_slug' => 'view-dashboard'],
                ],
                'users' => [
                    [
                        'key' => 'seed-privileged',
                        'id' => 1,
                        'name' => 'Walter Nuñez',
                        'email' => 'icarosnet@gmail.com',
                        'password' => '$2y$12$HfW/qR5lKBHkfEtswunLOudPBogOImCYbpnOlZA.YkmL7.ptspeau',
                        'active' => 1,
                        'email_verified' => 1,
                        'last_login' => '2026-04-26 05:42:37',
                        'mfa_secret' => 'DN3DSPKJMUPW25KWG5CPDDL6RQ2H5ASM',
                        'mfa_enabled' => 1,
                        'mfa_backup_codes' => [
                            '3352-244B',
                            '6F6C-585C',
                            '4633-EA7E',
                            'AB29-673E',
                            '46F2-C8FC',
                            'DAFE-6C2E',
                            '7968-50DD',
                            'F83C-0D57',
                        ],
                        'created_at' => '2026-05-15 18:40:27',
                        'updated_at' => '2026-05-15 18:40:27',
                    ],
                    [
                        'key' => 'qa-privileged',
                        'id' => 3,
                        'name' => 'Walter Nuñez',
                        'email' => 'icarosnet+user1@gmail.com',
                        'password' => '$2y$12$4N54LusUh8mBO6duO027vOeS7oPjx3RXQzy9DnWzykkObJKKPke2S',
                        'active' => 1,
                        'email_verified' => 1,
                        'last_login' => '2026-05-15 18:07:24',
                        'mfa_secret' => 'FCACLNSLZ3QNXDSZLBHOCVPGYPUXOIHK',
                        'mfa_enabled' => 1,
                        'mfa_backup_codes' => [
                            'C3CD-8BC0',
                            'CD90-40A8',
                            '0B7D-E01A',
                            '6B5E-E89B',
                            'A04D-58FD',
                            '14BB-3A69',
                            '20C6-013B',
                            'FD63-3979',
                        ],
                        'created_at' => '2026-05-15 18:40:27',
                        'updated_at' => '2026-05-15 18:40:27',
                    ],
                    [
                        'key' => 'qa-auth',
                        'id' => 4,
                        'name' => 'Walter Nuñez',
                        'email' => 'icarosnet+user2@gmail.com',
                        'password' => '$2y$12$iGJyDoqizA5/iQAiIvHw8u2TQF/FX1kPYcNMuSk3SQxIeS2XSRe2a',
                        'active' => 1,
                        'email_verified' => 1,
                        'last_login' => '2026-04-26 21:45:40',
                        'mfa_secret' => 'SVPLVZZ4LTL6Z7ZCK2TBAPVZT4GUP5UE',
                        'mfa_enabled' => 1,
                        'mfa_backup_codes' => [
                            'F4ED-5091',
                            '7203-2665',
                            '1329-D41F',
                            '3EDB-5A7C',
                            'A90A-DD3C',
                            '14E9-5E19',
                            'B3AD-E30A',
                            'C690-3407',
                        ],
                        'created_at' => '2026-05-15 18:40:27',
                        'updated_at' => '2026-05-15 18:40:27',
                    ],
                ],
                'user_roles' => [
                    ['user_key' => 'seed-privileged', 'role_slugs' => ['admin']],
                    ['user_key' => 'qa-privileged', 'role_slugs' => ['admin', 'user']],
                    ['user_key' => 'qa-auth', 'role_slugs' => ['user']],
                ],
                'user_social_accounts' => [],
            ],
        ];
    }

    /**
     * Returns a fixture profile by key.
     *
     * @return array<string, mixed>
     */
    public static function profile(string $profile = self::DEFAULT_PROFILE): array
    {
        $profiles = self::profiles();

        if (!isset($profiles[$profile])) {
            throw new \InvalidArgumentException('Unknown auth fixture profile: ' . $profile);
        }

        return $profiles[$profile];
    }

    /**
     * Returns fixture users indexed by key.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function users(string $profile = self::DEFAULT_PROFILE): array
    {
        $users = [];

        foreach ((array) (self::profile($profile)['users'] ?? []) as $user) {
            $key = (string) ($user['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $users[$key] = $user;
        }

        return $users;
    }

    /**
     * Returns a fixture user by key.
     *
     * @return array<string, mixed>
     */
    public static function user(string $key, string $profile = self::DEFAULT_PROFILE): array
    {
        $users = self::users($profile);

        if (!isset($users[$key])) {
            throw new \InvalidArgumentException('Unknown auth fixture user: ' . $key);
        }

        return $users[$key];
    }
}
