<?php

declare(strict_types=1);

namespace Catalyst\Framework\Mail;

use Catalyst\Helpers\Config\ConfigManager;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Validates, publishes and removes versionable framework-mail image assets.
 */
final class EmailAssetManager
{
    private const MAX_BYTES = 2_097_152;

    private const MIME_EXTENSIONS = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    private string $mailRoot;

    private string $publicRoot;

    private string $publicBaseUrl;

    public function __construct(
        ?string $mailRoot = null,
        ?string $publicRoot = null,
        ?string $publicBaseUrl = null
    ) {
        $projectRoot = dirname(__DIR__, 3);
        $this->mailRoot = rtrim(
            $mailRoot ?? $projectRoot . '/Repository/Framework/Mail',
            '/\\'
        );
        $this->publicRoot = rtrim(
            $publicRoot ?? $projectRoot . '/public/assets/work/framework-mail',
            '/\\'
        );
        $appUrl = $this->resolveApplicationUrl();
        $this->publicBaseUrl = rtrim(
            $publicBaseUrl ?? $appUrl . '/assets/work/framework-mail',
            '/'
        );
    }

    /**
     * @return array{name:string, mime:string, size:int, url:string}
     */
    public function storeManaged(string $sourcePath, string $originalName): array
    {
        if (!is_file($sourcePath)) {
            throw new InvalidArgumentException('Uploaded email asset is unavailable.');
        }
        $size = filesize($sourcePath);
        if (!is_int($size) || $size <= 0 || $size > self::MAX_BYTES) {
            throw new InvalidArgumentException('Email asset must be between 1 byte and 2 MB.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($sourcePath);
        if (!is_string($mime) || !isset(self::MIME_EXTENSIONS[$mime])) {
            throw new InvalidArgumentException('Email asset must be PNG, JPEG, WebP or GIF.');
        }

        $base = strtolower(pathinfo(basename($originalName), PATHINFO_FILENAME));
        $base = trim((string) preg_replace('/[^a-z0-9]+/', '-', $base), '-');
        if ($base === '') {
            throw new InvalidArgumentException('Email asset filename is invalid.');
        }
        $name = $base . '.' . self::MIME_EXTENSIONS[$mime];
        $sourceTarget = $this->mailRoot . '/managed/assets/' . $name;
        $publicTarget = $this->publicRoot . '/managed/' . $name;

        $this->atomicCopy($sourcePath, $sourceTarget);
        try {
            $this->atomicCopy($sourcePath, $publicTarget);
        } catch (\Throwable $throwable) {
            @unlink($sourceTarget);
            throw new RuntimeException('Email asset publication was rolled back.', 0, $throwable);
        }

        return [
            'name' => $name,
            'mime' => $mime,
            'size' => $size,
            'url' => $this->url($name, 'managed'),
        ];
    }

    /**
     * @return list<array{name:string, origin:string, size:int, url:string}>
     */
    public function list(): array
    {
        $assets = [];
        foreach (['system', 'managed'] as $origin) {
            foreach (glob($this->mailRoot . '/' . $origin . '/assets/*') ?: [] as $path) {
                if (!is_file($path)) {
                    continue;
                }
                $name = basename($path);
                $assets[] = [
                    'name' => $name,
                    'origin' => $origin,
                    'size' => (int) filesize($path),
                    'url' => $this->url($name, $origin),
                ];
            }
        }

        usort($assets, static fn (array $left, array $right): int => [$left['origin'], $left['name']]
            <=> [$right['origin'], $right['name']]);

        return $assets;
    }

    public function deleteManaged(string $name, EmailTemplateManager $templates): void
    {
        $name = $this->validateName($name);
        $references = $templates->assetReferences($name);
        if ($references !== []) {
            throw new RuntimeException(
                'Email asset is referenced by template(s): ' . implode(', ', $references)
            );
        }

        foreach ([
            $this->mailRoot . '/managed/assets/' . $name,
            $this->publicRoot . '/managed/' . $name,
        ] as $path) {
            if (is_file($path) && !unlink($path)) {
                throw new RuntimeException('Unable to remove managed email asset.');
            }
        }
    }

    public function publishAll(): void
    {
        foreach (['system', 'managed'] as $origin) {
            foreach (glob($this->mailRoot . '/' . $origin . '/assets/*') ?: [] as $path) {
                if (is_file($path)) {
                    $this->atomicCopy($path, $this->publicRoot . '/' . $origin . '/' . basename($path));
                }
            }
        }
    }

    public function url(string $name, ?string $origin = null): string
    {
        $name = $this->validateName($name);
        if ($origin === null) {
            $origin = is_file($this->mailRoot . '/managed/assets/' . $name) ? 'managed' : 'system';
        }
        if (!in_array($origin, ['system', 'managed'], true)) {
            throw new InvalidArgumentException('Unknown email asset origin.');
        }
        $source = $this->mailRoot . '/' . $origin . '/assets/' . $name;
        $public = $this->publicRoot . '/' . $origin . '/' . $name;
        if (is_file($source) && (!is_file($public) || filemtime($public) < filemtime($source))) {
            $this->atomicCopy($source, $public);
        }

        return $this->publicBaseUrl . '/' . $origin . '/' . rawurlencode($name);
    }

    private function validateName(string $name): string
    {
        if (basename($name) !== $name || preg_match('/^[a-z0-9][a-z0-9._-]*$/', $name) !== 1) {
            throw new InvalidArgumentException('Invalid email asset name.');
        }

        return $name;
    }

    private function atomicCopy(string $source, string $target): void
    {
        $this->assertTargetRoot($target);
        $directory = dirname($target);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create email asset directory.');
        }
        $temp = $target . '.tmp-' . bin2hex(random_bytes(6));
        if (!copy($source, $temp)) {
            throw new RuntimeException('Unable to stage email asset.');
        }
        if (!rename($temp, $target)) {
            @unlink($temp);
            throw new RuntimeException('Unable to publish email asset.');
        }
    }

    private function assertTargetRoot(string $target): void
    {
        $allowedRoot = str_starts_with(str_replace('\\', '/', $target), str_replace('\\', '/', $this->mailRoot))
            ? $this->mailRoot
            : $this->publicRoot;
        if (!is_dir($allowedRoot) && !mkdir($allowedRoot, 0775, true) && !is_dir($allowedRoot)) {
            throw new RuntimeException('Unable to create email asset root.');
        }
        $parent = dirname($target);
        while (!is_dir($parent) && dirname($parent) !== $parent) {
            $parent = dirname($parent);
        }
        $rootReal = realpath($allowedRoot);
        $parentReal = realpath($parent);
        if (!is_string($rootReal) || !is_string($parentReal)) {
            throw new RuntimeException('Unable to validate email asset target.');
        }
        $rootReal = rtrim(str_replace('\\', '/', $rootReal), '/') . '/';
        $parentReal = rtrim(str_replace('\\', '/', $parentReal), '/') . '/';
        if (!str_starts_with($parentReal, $rootReal)) {
            throw new RuntimeException('Email asset target escaped through a linked directory.');
        }
    }

    private function resolveApplicationUrl(): string
    {
        try {
            $projectUrl = trim((string) (
                ConfigManager::getInstance()->entry('app', 'project')['project_url'] ?? ''
            ));
            if ($projectUrl !== '') {
                return rtrim($projectUrl, '/');
            }
        } catch (Throwable) {
        }

        $environment = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];

        return rtrim((string) ($environment['APP_URL'] ?? getenv('APP_URL') ?: ''), '/');
    }
}
