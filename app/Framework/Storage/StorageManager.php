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
 * Resolves configured storage disks and delegates object operations.
 *
 * @package Catalyst\Framework\Storage
 * Responsibility: Provides a stable facade over local, runtime and remote storage adapters.
 */
final class StorageManager
{
    use SingletonTrait;

    /** @var array<string, StorageAdapterInterface> */
    private array $disks = [];

    private string $fingerprint = '';

    /**
     * Returns a configured storage disk by name.
     *
     * Responsibility: Returns a configured storage disk by name.
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
     * Stores string contents on a selected disk.
     *
     * Responsibility: Stores string contents on a selected disk.
     */
    public function put(string $path, string $contents, string $disk = 'local'): string
    {
        return $this->disk($disk)->put($path, $contents);
    }

    /**
     * Stores an uploaded file on a selected disk.
     *
     * Responsibility: Stores an uploaded file on a selected disk.
     */
    public function putUploadedFile(UploadedFile $file, string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->putFile($file, $path);
    }

    /**
     * Reads object contents from a selected disk.
     *
     * Responsibility: Reads object contents from a selected disk.
     */
    public function get(string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->get($path);
    }

    /**
     * Deletes an object from a selected disk.
     *
     * Responsibility: Deletes an object from a selected disk.
     */
    public function delete(string $path, string $disk = 'local'): bool
    {
        return $this->disk($disk)->delete($path);
    }

    /**
     * Determines whether an object exists on a selected disk.
     *
     * Responsibility: Determines whether an object exists on a selected disk.
     */
    public function exists(string $path, string $disk = 'local'): bool
    {
        return $this->disk($disk)->exists($path);
    }

    /**
     * Returns the public or remote URL for an object.
     *
     * Responsibility: Returns the public or remote URL for an object.
     */
    public function url(string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->url($path);
    }

    /**
     * Clears cached storage adapters so configuration is reloaded.
     *
     * Responsibility: Clears cached storage adapters so configuration is reloaded.
     */
    public function refresh(): void
    {
        $this->disks = [];
        $this->fingerprint = '';
    }

    /**
     * Builds the configured disk map when configuration changes.
     *
     * Responsibility: Builds the configured disk map when configuration changes.
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
     * Builds and caches configured disk adapters.
     *
     * Responsibility: Builds and caches configured disk adapters.
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
