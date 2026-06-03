<?php

declare(strict_types=1);

namespace Catalyst\Framework\Release;

/**
 * Reads and compares Catalyst release metadata.
 */
final class ReleaseMetadata
{
    /**
     * @return array{version:string,channel:string,source:string,release_manifest:string}
     */
    public static function local(): array
    {
        return self::fromFile(PD . DS . 'catalyst.json');
    }

    /**
     * @return array{version:string,channel:string,source:string,release_manifest:string}
     */
    public static function fromFile(string $path): array
    {
        if (!is_file($path)) {
            throw new \RuntimeException('Release metadata file not found: ' . $path);
        }

        $json = file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException('Unable to read release metadata file: ' . $path);
        }

        return self::decode($json, $path);
    }

    /**
     * @return array{version:string,channel:string,source:string,release_manifest:string}
     */
    public static function fromUrl(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $json = @file_get_contents($url, false, $context);
        if ($json === false) {
            throw new \RuntimeException('Unable to read release manifest URL: ' . $url);
        }

        return self::decode($json, $url);
    }

    public static function isRemote(string $manifest): bool
    {
        return preg_match('#^https?://#i', $manifest) === 1;
    }

    /**
     * @param array{version:string} $current
     * @param array{version:string} $latest
     */
    public static function updateAvailable(array $current, array $latest): bool
    {
        return version_compare(self::normalizeVersion($latest['version']), self::normalizeVersion($current['version']), '>');
    }

    private static function normalizeVersion(string $version): string
    {
        return ltrim(trim($version), 'vV');
    }

    /**
     * @return array{version:string,channel:string,source:string,release_manifest:string}
     */
    private static function decode(string $json, string $source): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid release metadata JSON in ' . $source . ': ' . json_last_error_msg());
        }

        $version = trim((string) ($data['version'] ?? ''));
        if ($version === '') {
            throw new \RuntimeException('Release metadata missing version in ' . $source);
        }

        return [
            'version' => $version,
            'channel' => trim((string) ($data['channel'] ?? 'unknown')),
            'source' => trim((string) ($data['source'] ?? '')),
            'release_manifest' => trim((string) ($data['release_manifest'] ?? '')),
        ];
    }
}
