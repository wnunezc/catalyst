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

namespace Catalyst\Framework\Http;

/**
 * Sanitizes internal redirect targets.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Rejects unsafe redirect destinations and builds login redirect URLs for protected surfaces.
 */
final class RedirectTarget
{
    /**
     * @var string[]
     */
    private const AUTH_SURFACE_PATHS = [
        '/',
        '/login',
        '/register',
        '/forgot-password',
        '/verify-email',
    ];

    /**
     * Returns a safe internal redirect path or the fallback path.
     */
    public static function clean(mixed $target, string $fallback = '/'): string
    {
        $fallback = self::fallbackPath($fallback);
        $value = trim((string) $target);

        if ($value === '' || str_starts_with($value, '//') || str_contains($value, '\\')) {
            return $fallback;
        }

        if (preg_match('/\A[a-z][a-z0-9+.-]*:/i', $value) === 1 || !str_starts_with($value, '/')) {
            return $fallback;
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return $fallback;
        }

        $query = parse_url($value, PHP_URL_QUERY);

        return $query !== null && $query !== ''
            ? $path . '?' . $query
            : $path;
    }

    /**
     * Builds the login URL with a safe redirect target.
     */
    public static function loginUrl(mixed $target, string $fallback = '/'): string
    {
        $safeTarget = self::clean($target, $fallback);
        $path = (string) (parse_url($safeTarget, PHP_URL_PATH) ?: '/');

        if (in_array($path, self::AUTH_SURFACE_PATHS, true)) {
            return '/login';
        }

        return '/login?redirect=' . str_replace('%2F', '/', rawurlencode($safeTarget));
    }

    /**
     * Normalizes fallback paths to an internal absolute path.
     */
    private static function fallbackPath(string $fallback): string
    {
        $fallback = trim($fallback);

        return $fallback !== '' && str_starts_with($fallback, '/') && !str_starts_with($fallback, '//')
            ? $fallback
            : '/';
    }
}
