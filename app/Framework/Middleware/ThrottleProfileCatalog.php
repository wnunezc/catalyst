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

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Route\Route;

/**
 * Catalogs built-in request throttling profiles.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Resolves route-specific throttle configuration or derives a default profile from the request path.
 */
final class ThrottleProfileCatalog
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private const PROFILES = [
        'default_mutation' => [
            'name' => 'default_mutation',
            'enabled' => true,
            'max_attempts' => 60,
            'window_seconds' => 60,
            'lockout_seconds' => 120,
            'scope' => 'actor',
            'context' => 'mutation',
            'route_scoped' => true,
        ],
        'setup_mutation' => [
            'name' => 'setup_mutation',
            'enabled' => true,
            'max_attempts' => 40,
            'window_seconds' => 60,
            'lockout_seconds' => 180,
            'scope' => 'actor',
            'context' => 'setup',
            'route_scoped' => true,
        ],
        'privileged_mutation' => [
            'name' => 'privileged_mutation',
            'enabled' => true,
            'max_attempts' => 30,
            'window_seconds' => 60,
            'lockout_seconds' => 180,
            'scope' => 'actor',
            'context' => 'privileged',
            'route_scoped' => true,
        ],
        'api_mutation' => [
            'name' => 'api_mutation',
            'enabled' => true,
            'max_attempts' => 90,
            'window_seconds' => 60,
            'lockout_seconds' => 120,
            'scope' => 'actor',
            'context' => 'api',
            'route_scoped' => true,
        ],
        'presence_heartbeat' => [
            'name' => 'presence_heartbeat',
            'enabled' => true,
            'max_attempts' => 30,
            'window_seconds' => 60,
            'lockout_seconds' => 60,
            'scope' => 'actor',
            'context' => 'presence',
            'route_scoped' => true,
        ],
        'auth_recovery' => [
            'name' => 'auth_recovery',
            'enabled' => true,
            'max_attempts' => 6,
            'window_seconds' => 300,
            'lockout_seconds' => 600,
            'scope' => 'ip',
            'context' => 'auth-recovery',
            'route_scoped' => true,
        ],
        'mfa_challenge' => [
            'name' => 'mfa_challenge',
            'enabled' => true,
            'max_attempts' => 8,
            'window_seconds' => 300,
            'lockout_seconds' => 600,
            'scope' => 'actor',
            'context' => 'mfa',
            'route_scoped' => true,
        ],
        'disabled' => [
            'name' => 'disabled',
            'enabled' => false,
            'max_attempts' => 0,
            'window_seconds' => 0,
            'lockout_seconds' => 0,
            'scope' => 'actor',
            'context' => 'disabled',
            'route_scoped' => false,
        ],
    ];

    /**
     * Resolves the effective throttle profile for a route and path.
     *
     * @return array<string, mixed>
     */
    public static function resolve(?Route $route, string $path): array
    {
        $rawProfile = $route?->getAttribute('throttle');

        if (is_string($rawProfile) && isset(self::PROFILES[$rawProfile])) {
            return self::PROFILES[$rawProfile];
        }

        if (is_array($rawProfile)) {
            return array_replace(self::PROFILES['default_mutation'], $rawProfile, [
                'name' => (string) ($rawProfile['name'] ?? 'custom'),
            ]);
        }

        return self::deriveDefaultForPath($path);
    }

    /**
     * Derives the built-in throttle profile associated with a path prefix.
     *
     * @return array<string, mixed>
     */
    private static function deriveDefaultForPath(string $path): array
    {
        foreach ([
            '/configuration/environment-setup' => 'setup_mutation',
            '/users' => 'privileged_mutation',
            '/forgot-password' => 'auth_recovery',
            '/reset-password' => 'auth_recovery',
            '/mfa/verify' => 'mfa_challenge',
            '/mfa/enable' => 'mfa_challenge',
            '/mfa/disable' => 'mfa_challenge',
        ] as $prefix => $profile) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return self::PROFILES[$profile];
            }
        }

        return self::PROFILES['default_mutation'];
    }
}
