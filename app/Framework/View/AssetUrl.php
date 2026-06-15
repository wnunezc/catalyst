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
        return self::withVersion($url, self::fileVersion($url, $publicRoot));
    }

    /**
     * Versions an entry asset from the publication metadata of its complete local dependency tree.
     *
     * Responsibility: Invalidates browser module caches when any published dependency below the tree changes.
     */
    public static function versionedTree(string $url, string $treeUrl, ?string $publicRoot = null): string
    {
        return self::withVersion($url, self::treeVersion($treeUrl, $publicRoot));
    }

    private static function fileVersion(string $url, ?string $publicRoot): string
    {
        $parts = parse_url($url);
        if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host'])) {
            return '0';
        }

        $path = (string) ($parts['path'] ?? '');
        if (!str_starts_with($path, '/assets/')) {
            return '0';
        }

        $filesystemPath = self::filesystemPath($path, $publicRoot);
        $modifiedAt = is_file($filesystemPath) ? filemtime($filesystemPath) : false;

        return is_int($modifiedAt) ? (string) $modifiedAt : '0';
    }

    private static function treeVersion(string $treeUrl, ?string $publicRoot): string
    {
        $treePath = self::filesystemPath((string) (parse_url($treeUrl, PHP_URL_PATH) ?: ''), $publicRoot);
        if (!is_dir($treePath)) {
            return '0';
        }

        $metadata = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($treePath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($treePath) + 1);
            $metadata[] = str_replace('\\', '/', $relativePath)
                . ':' . $file->getSize()
                . ':' . $file->getMTime();
        }

        sort($metadata);

        return substr(hash('sha256', implode("\n", $metadata)), 0, 16);
    }

    private static function withVersion(string $url, string $version): string
    {
        $parts = parse_url($url);
        if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host'])) {
            return $url;
        }

        $path = (string) ($parts['path'] ?? '');
        if (!str_starts_with($path, '/assets/')) {
            return $url;
        }

        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        $query['v'] = $version;

        $versioned = $path . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        if (isset($parts['fragment'])) {
            $versioned .= '#' . $parts['fragment'];
        }

        return $versioned;
    }

    private static function filesystemPath(string $path, ?string $publicRoot): string
    {
        $decodedPath = rawurldecode($path);
        $segments = array_values(array_filter(
            preg_split('#[\\\\/]+#', $decodedPath) ?: [],
            static fn (string $segment): bool => $segment !== ''
        ));

        if ($segments === [] || in_array('..', $segments, true)) {
            return '';
        }

        $root = $publicRoot ?? self::defaultPublicRoot();

        return rtrim($root, '/\\') . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments);
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
