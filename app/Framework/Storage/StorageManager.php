<?php

declare(strict_types=1);

namespace Catalyst\Framework\Storage;

use Catalyst\Framework\Http\UploadedFile;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use RuntimeException;

final class StorageManager
{
    use SingletonTrait;

    /** @var array<string, StorageAdapterInterface> */
    private array $disks = [];

    private string $fingerprint = '';

    public function disk(string $name = 'local'): StorageAdapterInterface
    {
        $disks = $this->disks();

        if (!isset($disks[$name])) {
            throw new RuntimeException(sprintf('Storage disk "%s" is not configured.', $name));
        }

        return $disks[$name];
    }

    public function put(string $path, string $contents, string $disk = 'local'): string
    {
        return $this->disk($disk)->put($path, $contents);
    }

    public function putUploadedFile(UploadedFile $file, string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->putFile($file, $path);
    }

    public function get(string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->get($path);
    }

    public function delete(string $path, string $disk = 'local'): bool
    {
        return $this->disk($disk)->delete($path);
    }

    public function exists(string $path, string $disk = 'local'): bool
    {
        return $this->disk($disk)->exists($path);
    }

    public function url(string $path, string $disk = 'local'): string
    {
        return $this->disk($disk)->url($path);
    }

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
                'ftp' => new FtpStorageAdapter($ftp),
            ];
        }

        return $this->disks;
    }
}
