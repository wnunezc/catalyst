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

namespace Catalyst\Framework\Storage;

use Catalyst\Framework\Http\UploadedFile;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use RuntimeException;

/**
 * Defines the Storage Manager class contract.
 *
 * @package Catalyst\Framework\Storage
 * Responsibility: Coordinates the storage manager behavior within its module boundary.
 */
final class StorageManager
{
    use SingletonTrait;

    /** @var array<string, StorageAdapterInterface> */
    private array $disks = [];

    private string $fingerprint = '';

    /**
     * Handles the disk workflow.
     */
    public function disk(string $name = 'local'): StorageAdapterInterface
    {
        $disks = $this->disks();

        if (!isset($disks[$name])) {
            throw new RuntimeException(sprintf('Storage disk "%s" is not configured.', $name));
        }

        return $disks[$name];
    }

    /**
     * Handles the put workflow.
     */
    public function put(string $path, string $contents, string $disk = 'local'): string
    {
        return $this->disk($disk)->put($path, $contents);
    }

    /**
     * Handles the put uploaded file workflow.
     */
    public function putUploadedFile(UploadedFile $file, string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->putFile($file, $path);
    }

    /**
     * Returns the runtime value.
     */
    public function get(string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->get($path);
    }

    /**
     * Handles the delete workflow.
     */
    public function delete(string $path, string $disk = 'local'): bool
    {
        return $this->disk($disk)->delete($path);
    }

    /**
     * Handles the exists workflow.
     */
    public function exists(string $path, string $disk = 'local'): bool
    {
        return $this->disk($disk)->exists($path);
    }

    /**
     * Handles the url workflow.
     */
    public function url(string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->url($path);
    }

    /**
     * Handles the refresh workflow.
     */
    public function refresh(): void
    {
        $this->disks = [];
        $this->fingerprint = '';
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $ftp = ConfigManager::getInstance()->entry('ftp', 'ftp1');
        $remoteReady = trim((string) ($ftp['ftp_host'] ?? '')) !== ''
            && trim((string) ($ftp['ftp_username'] ?? '')) !== ''
            && trim((string) ($ftp['ftp_password'] ?? '')) !== '';

        return [
            'default_disk' => 'local',
            'local_driver' => $this->disk('local')->getDriverName(),
            'local_root' => implode(DIRECTORY_SEPARATOR, [PD, 'public']),
            'runtime_driver' => $this->disk('runtime')->getDriverName(),
            'runtime_root' => implode(DIRECTORY_SEPARATOR, [PD, 'boot-core', 'storage', 'runtime']),
            'runtime_public' => false,
            'remote_driver' => trim((string) ($ftp['ftp_protocol'] ?? 'ftp')) ?: 'ftp',
            'remote_ready' => $remoteReady,
            'remote_root' => (string) ($ftp['ftp_root'] ?? '/'),
        ];
    }

    /**
     * @return array<string, StorageAdapterInterface>
     */
    private function disks(): array
    {
        $ftp = ConfigManager::getInstance()->entry('ftp', 'ftp1');
        $fingerprint = sha1(json_encode([
            'ftp_protocol' => (string) ($ftp['ftp_protocol'] ?? 'ftp'),
            'ftp_host' => (string) ($ftp['ftp_host'] ?? ''),
            'ftp_port' => (int) ($ftp['ftp_port'] ?? 21),
            'ftp_username' => (string) ($ftp['ftp_username'] ?? ''),
            'ftp_password' => (string) ($ftp['ftp_password'] ?? ''),
            'ftp_root' => (string) ($ftp['ftp_root'] ?? '/'),
            'ftp_timeout' => (int) ($ftp['ftp_timeout'] ?? 10),
            'ftp_passive' => (bool) ($ftp['ftp_passive'] ?? true),
        ], JSON_THROW_ON_ERROR));

        if ($this->fingerprint !== $fingerprint) {
            $this->fingerprint = $fingerprint;
            $this->disks = [
                'local' => new LocalStorageAdapter(implode(DIRECTORY_SEPARATOR, [PD, 'public']), '/'),
                'runtime' => new LocalStorageAdapter(
                    implode(DIRECTORY_SEPARATOR, [PD, 'boot-core', 'storage', 'runtime']),
                    '',
                    false
                ),
                'ftp' => new FtpStorageAdapter($ftp),
            ];
        }

        return $this->disks;
    }
}
