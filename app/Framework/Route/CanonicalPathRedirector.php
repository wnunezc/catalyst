<?php

declare(strict_types=1);

namespace Catalyst\Framework\Route;

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

    private function matchesPrefix(string $path, string $prefix): bool
    {
        return $path === $prefix
            || str_starts_with($path, $prefix . '/');
    }

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
