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

namespace Catalyst\Framework\Deployment;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Health\HealthReportBuilder;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

/**
 * Defines the Deployment Manager class contract.
 *
 * @package Catalyst\Framework\Deployment
 * Responsibility: Coordinates the deployment manager behavior within its module boundary.
 */
final class DeploymentManager
{
    use SingletonTrait;

    private const EXCLUDES = [
        'boot-core/config/env/.env',
        'boot-core/config/env/.env.example',
        'boot-core/config/env/.env.ver',
        'boot-core/config/dkim',
        'boot-core/storage/logs',
        'boot-core/storage/throttle',
        'public/uploads/devtools',
        '.idea',
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function profiles(): array
    {
        $section = ConfigManager::getInstance()->section('deployments') ?? [];
        $profiles = $section['profiles'] ?? $section;

        return is_array($profiles) ? $profiles : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'profiles' => array_keys($this->profiles()),
            'run_count' => count(DeploymentRunRepository::getInstance()->all()),
            'release_root' => $this->releaseRoot(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function run(string $profileKey, bool $dryRun = false): array
    {
        $profiles = $this->profiles();
        $profile = $profiles[$profileKey] ?? null;
        if (!is_array($profile)) {
            throw new RuntimeException(sprintf('Deployment profile "%s" is not defined.', $profileKey));
        }

        $releaseId = gmdate('YmdHis') . '-' . preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($profileKey));
        $releaseDir = $this->releaseRoot() . DS . $releaseId;
        $artifactDir = $releaseDir . DS . 'staging';
        $zipPath = $releaseDir . DS . 'release.zip';
        $preflight = (new HealthReportBuilder())->build();
        $actorId = $this->resolveActorId();

        if (!is_dir($releaseDir) && !mkdir($releaseDir, 0755, true) && !is_dir($releaseDir)) {
            throw new RuntimeException('Could not create release directory: ' . $releaseDir);
        }

        $run = DeploymentRunRepository::getInstance()->create([
            'profile_key' => $profileKey,
            'release_id' => $releaseId,
            'environment' => ConfigManager::getInstance()->getEnvironment(),
            'status' => $dryRun ? 'dry-run' : 'running',
            'dry_run' => $dryRun,
            'artifact_path' => $artifactDir,
            'remote_path' => null,
            'summary_json' => [
                'preflight' => $preflight['summary'] ?? [],
                'profile' => $profile,
            ],
            'started_at' => gmdate('Y-m-d H:i:s'),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);

        $result = [
            'run_id' => (int) $run->getKey(),
            'profile_key' => $profileKey,
            'release_id' => $releaseId,
            'dry_run' => $dryRun,
            'artifact_path' => $artifactDir,
            'zip_path' => null,
            'remote_path' => null,
            'preflight' => $preflight,
            'copied_files' => 0,
            'published' => false,
        ];

        try {
            if (!$dryRun) {
                $result['copied_files'] = $this->copyWorkspace($artifactDir, (array) ($profile['exclude'] ?? []));

                if (!empty($profile['create_zip'])) {
                    $result['zip_path'] = $this->buildZip($artifactDir, $zipPath);
                }

                if (!empty($profile['publish_remote'])) {
                    $result['remote_path'] = $this->publishRemote(
                        (array) $profile,
                        (string) ($result['zip_path'] ?? $artifactDir),
                        $releaseId
                    );
                    $result['published'] = $result['remote_path'] !== null;
                }
            }

            $summary = [
                'preflight' => $preflight['summary'] ?? [],
                'copied_files' => $result['copied_files'],
                'published' => $result['published'],
                'zip_path' => $result['zip_path'],
                'remote_path' => $result['remote_path'],
            ];

            DeploymentRunRepository::getInstance()->update($run, [
                'status' => $dryRun ? 'dry-run' : 'completed',
                'artifact_path' => $artifactDir,
                'remote_path' => $result['remote_path'],
                'summary_json' => $summary,
                'finished_at' => gmdate('Y-m-d H:i:s'),
                'updated_by' => $actorId,
            ]);

            return $result;
        } catch (\Throwable $e) {
            DeploymentRunRepository::getInstance()->update($run, [
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => gmdate('Y-m-d H:i:s'),
                'updated_by' => $actorId,
            ]);

            throw $e;
        }
    }

    /**
     * Handles the release root workflow.
     */
    private function releaseRoot(): string
    {
        return implode(DS, [PD, 'boot-core', 'storage', 'releases']);
    }

    /**
     * Resolves the requested value.
     */
    private function resolveActorId(): ?int
    {
        try {
            return AuthManager::getInstance()->id();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param string[] $extraExcludes
     */
    private function copyWorkspace(string $targetRoot, array $extraExcludes): int
    {
        $sourceRoot = PD;
        $copied = 0;
        $excludes = array_values(array_unique(array_merge(self::EXCLUDES, $extraExcludes)));

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $path = (string) $item->getPathname();
            $relative = ltrim(str_replace($sourceRoot, '', $path), DIRECTORY_SEPARATOR);
            $normalized = str_replace('\\', '/', $relative);

            if ($normalized === '' || $this->isExcluded($normalized, $excludes)) {
                continue;
            }

            $target = $targetRoot . DS . $relative;

            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                    throw new RuntimeException('Could not create deployment staging directory: ' . $target);
                }

                continue;
            }

            $targetDir = dirname($target);
            if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new RuntimeException('Could not create deployment target directory: ' . $targetDir);
            }

            if (!copy($path, $target)) {
                throw new RuntimeException('Could not copy deployment artifact file: ' . $relative);
            }

            $copied++;
        }

        return $copied;
    }

    /**
     * @param string[] $excludes
     */
    private function isExcluded(string $relativePath, array $excludes): bool
    {
        foreach ($excludes as $exclude) {
            $exclude = trim(str_replace('\\', '/', $exclude), '/');
            if ($exclude === '') {
                continue;
            }

            if ($relativePath === $exclude || str_starts_with($relativePath, $exclude . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Builds the requested structure.
     */
    private function buildZip(string $sourceDir, string $zipPath): ?string
    {
        if (!class_exists(ZipArchive::class)) {
            return null;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create deployment ZIP artifact.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (!$item->isFile()) {
                continue;
            }

            $path = (string) $item->getPathname();
            $relative = ltrim(str_replace($sourceDir, '', $path), DIRECTORY_SEPARATOR);
            $zip->addFile($path, str_replace('\\', '/', $relative));
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function publishRemote(array $profile, string $artifactPath, string $releaseId): ?string
    {
        $disk = trim((string) ($profile['disk'] ?? 'ftp'));
        $remoteDir = trim((string) ($profile['remote_directory'] ?? 'releases'), '/');

        $payload = is_file($artifactPath)
            ? file_get_contents($artifactPath)
            : json_encode(['release_id' => $releaseId, 'artifact_path' => $artifactPath], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new RuntimeException('Could not read deployment artifact for remote publish.');
        }

        $remotePath = $remoteDir . '/' . basename(is_file($artifactPath) ? $artifactPath : ($releaseId . '.json'));
        StorageManager::getInstance()->put($remotePath, (string) $payload, $disk);

        return $remotePath;
    }
}
