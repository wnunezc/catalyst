<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

/**
 * Builds deterministic cache-busted URLs for local public assets.
 *
 * Responsibility: Maps local `/assets/*` URLs to published files and versions them from stable publication metadata.
 */
final class AssetUrl
{
    /**
     * Appends or replaces the version query parameter for a local public asset.
     *
     * Responsibility: Preserves URL query and fragment data while deriving a stable version from the published file.
     */
    public static function versioned(string $url, ?string $publicRoot = null): string
    {
        $parts = parse_url($url);
        if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host'])) {
            return $url;
        }

        $path = (string) ($parts['path'] ?? '');
        if (!str_starts_with($path, '/assets/')) {
            return $url;
        }

        $decodedPath = rawurldecode($path);
        $segments = array_values(array_filter(
            preg_split('#[\\\\/]+#', $decodedPath) ?: [],
            static fn (string $segment): bool => $segment !== ''
        ));

        if ($segments === [] || in_array('..', $segments, true)) {
            return $url;
        }

        $root = $publicRoot ?? self::defaultPublicRoot();
        $filesystemPath = rtrim($root, '/\\') . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments);
        $modifiedAt = is_file($filesystemPath) ? filemtime($filesystemPath) : false;
        $version = is_int($modifiedAt) ? (string) $modifiedAt : '0';

        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        $query['v'] = $version;

        $versioned = $path . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        if (isset($parts['fragment'])) {
            $versioned .= '#' . $parts['fragment'];
        }

        return $versioned;
    }

    /**
     * Resolves the public directory for the active Catalyst project.
     *
     * Responsibility: Keeps project-root discovery out of callers that only need a versioned public asset URL.
     */
    private static function defaultPublicRoot(): string
    {
        if (defined('PD')) {
            return PD . DIRECTORY_SEPARATOR . 'public';
        }

        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public';
    }
}
