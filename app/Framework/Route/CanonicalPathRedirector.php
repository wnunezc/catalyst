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

namespace Catalyst\Framework\Route;

/**
 * Resolves legacy paths to their canonical public routes.
 *
 * @package Catalyst\Framework\Route
 * Responsibility: Normalizes incoming paths and returns redirects only when a legacy route prefix maps to a different canonical path.
 */
final class CanonicalPathRedirector
{
    /**
     * @var array<string, string>
     */
    private const PREFIX_MAP = [
        '/configuration/environment-setup' => '/configuration/environment-setup',
        '/configuration/application-health' => '/configuration/application-health',
        '/configuration/platform-appearance' => '/configuration/platform-appearance',
        '/configuration/feature-flags' => '/configuration/feature-flags',
        '/configuration/plugins' => '/configuration/plugins',
        '/workspaces/module-designer' => '/workspaces/module-designer',
        '/workspaces/document-templates' => '/workspaces/document-templates',
        '/workspaces/media-library' => '/workspaces/media-library',
        '/workspaces/media-fields' => '/workspaces/media-fields',
        '/workspaces/locale-tools' => '/workspaces/locale-tools',
        '/workspaces/catalogs' => '/workspaces/catalogs',
        '/operations/deployments' => '/operations/deployments',
        '/operations/tenancy' => '/operations/tenancy',
        '/users/enroll' => '/users/enroll',
        '/users/permissions' => '/users/permissions',
        '/users/roles' => '/users/roles',
        '/users' => '/users',
        '/dashboard' => '/dashboard',
        '/landing' => '/landing',
        '/store' => '/store',
        '/home' => '/home',
        '/demo-ui' => '/demo-ui',
    ];

    /**
     * Returns the canonical redirect target for a legacy URI when required.
     *
     * Responsibility: Returns the canonical redirect target for a legacy URI when required.
     */
    public function redirectTarget(string $uri): ?string
    {
        $path = $this->normalizePath((string) (parse_url($uri, PHP_URL_PATH) ?: $uri));
        $normalizedPath = strtolower($path);
        $query = trim((string) (parse_url($uri, PHP_URL_QUERY) ?: ''));

        foreach ($this->prefixMap() as $source => $target) {
            if (!$this->matchesPrefix($normalizedPath, $source)) {
                continue;
            }

            $suffix = substr($normalizedPath, strlen($source));
            $canonicalPath = $target . $suffix;

            if ($canonicalPath === $path) {
                return null;
            }

            return $query === ''
                ? $canonicalPath
                : $canonicalPath . '?' . $query;
        }

        return null;
    }

    /**
     * Returns only prefix mappings that redirect legacy routes.
     *
     * Responsibility: Returns only prefix mappings that redirect legacy routes.
     * @return array<string, string>
     */
    public function legacyPrefixes(): array
    {
        return array_filter(
            $this->prefixMap(),
            static fn (string $target, string $source): bool => $source !== $target,
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Returns all prefix mappings ordered from most specific to least specific.
     *
     * Responsibility: Returns all prefix mappings ordered from most specific to least specific.
     * @return array<string, string>
     */
    private function prefixMap(): array
    {
        $map = self::PREFIX_MAP;
        uksort(
            $map,
            static fn (string $left, string $right): int => strlen($right) <=> strlen($left)
        );

        return $map;
    }

    /**
     * Determines whether a path equals or descends from a mapped prefix.
     *
     * Responsibility: Determines whether a path equals or descends from a mapped prefix.
     */
    private function matchesPrefix(string $path, string $prefix): bool
    {
        return $path === $prefix
            || str_starts_with($path, $prefix . '/');
    }

    /**
     * Normalizes a path to a rooted form without a trailing slash.
     *
     * Responsibility: Normalizes a path to a rooted form without a trailing slash.
     */
    private function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return $path === '/' ? $path : rtrim($path, '/');
    }
}
