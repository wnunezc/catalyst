<?php

declare(strict_types=1);

namespace Catalyst\Framework\Http;

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

    public static function loginUrl(mixed $target, string $fallback = '/'): string
    {
        $safeTarget = self::clean($target, $fallback);
        $path = (string) (parse_url($safeTarget, PHP_URL_PATH) ?: '/');

        if (in_array($path, self::AUTH_SURFACE_PATHS, true)) {
            return '/login';
        }

        return '/login?redirect=' . rawurlencode($safeTarget);
    }

    private static function fallbackPath(string $fallback): string
    {
        $fallback = trim($fallback);

        return $fallback !== '' && str_starts_with($fallback, '/') && !str_starts_with($fallback, '//')
            ? $fallback
            : '/';
    }
}
