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
 * Defines the Canonical Path Redirector class contract.
 *
 * @package Catalyst\Framework\Route
 * Responsibility: Coordinates the canonical path redirector behavior within its module boundary.
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
     * Handles the redirect target workflow.
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
     * Handles the matches prefix workflow.
     */
    private function matchesPrefix(string $path, string $prefix): bool
    {
        return $path === $prefix
            || str_starts_with($path, $prefix . '/');
    }

    /**
     * Normalizes the provided value.
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
