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
use RuntimeException;

/**
 * Defines the Ftp Storage Adapter class contract.
 *
 * @package Catalyst\Framework\Storage
 * Responsibility: Coordinates the ftp storage adapter behavior within its module boundary.
 */
final class FtpStorageAdapter implements StorageAdapterInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly array $config
    ) {
    }

    /**
     * Returns the driver name value.
     */
    public function getDriverName(): string
    {
        return strtolower(trim((string) ($this->config['ftp_protocol'] ?? 'ftp')));
    }

    /**
     * Handles the put workflow.
     */
    public function put(string $path, string $contents): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'catalyst-storage-');
        if ($temp === false) {
            throw new RuntimeException('Unable to create temporary storage payload.');
        }

        try {
            if (@file_put_contents($temp, $contents) === false) {
                throw new RuntimeException('Unable to write temporary storage payload.');
            }

            return $this->uploadLocalPath($temp, $path);
        } finally {
            @unlink($temp);
        }
    }

    /**
     * Handles the put file workflow.
     */
    public function putFile(UploadedFile $file, string $path): string
    {
        if (!$file->isValid()) {
            throw new RuntimeException($file->getErrorMessage());
        }

        return $this->uploadLocalPath($file->getPath(), $path);
    }

    /**
     * Returns the runtime value.
     */
    public function get(string $path): string
    {
        return match ($this->protocol()) {
            'sftp' => $this->downloadSftp($path),
            default => $this->downloadFtp($path),
        };
    }

    /**
     * Handles the delete workflow.
     */
    public function delete(string $path): bool
    {
        return match ($this->protocol()) {
            'sftp' => $this->deleteSftp($path),
            default => $this->deleteFtp($path),
        };
    }

    /**
     * Handles the exists workflow.
     */
    public function exists(string $path): bool
    {
        return match ($this->protocol()) {
            'sftp' => $this->existsSftp($path),
            default => $this->existsFtp($path),
        };
    }

    /**
     * Handles the url workflow.
     */
    public function url(string $path): string
    {
        return sprintf(
            '%s://%s:%d%s',
            $this->protocol(),
            $this->requiredString('ftp_host'),
            $this->port(),
            $this->remotePath($path)
        );
    }

    /**
     * Handles the upload local path workflow.
     */
    private function uploadLocalPath(string $localPath, string $path): string
    {
        if (!is_file($localPath)) {
            throw new RuntimeException('Upload source file does not exist.');
        }

        return match ($this->protocol()) {
            'sftp' => $this->uploadSftp($localPath, $path),
            default => $this->uploadFtp($localPath, $path),
        };
    }

    /**
     * Handles the upload ftp workflow.
     */
    private function uploadFtp(string $localPath, string $path): string
    {
        if (!function_exists('ftp_connect')) {
            throw new RuntimeException('The PHP FTP extension is not available in this runtime.');
        }

        if ($this->protocol() === 'ftps' && !function_exists('ftp_ssl_connect')) {
            throw new RuntimeException('The PHP FTP SSL extension is not available in this runtime.');
        }

        $remotePath = $this->remotePath($path);
        $directory = dirname($remotePath);
        $filename = basename($remotePath);
        $connection = $this->protocol() === 'ftps'
            ? @ftp_ssl_connect($this->requiredString('ftp_host'), $this->port(), $this->timeout())
            : @ftp_connect($this->requiredString('ftp_host'), $this->port(), $this->timeout());

        if ($connection === false) {
            throw new RuntimeException('Unable to connect to the configured FTP server.');
        }

        try {
            if (@ftp_login($connection, $this->requiredString('ftp_username'), $this->requiredString('ftp_password')) === false) {
                throw new RuntimeException('FTP authentication failed with the configured credentials.');
            }

            @ftp_pasv($connection, (bool) ($this->config['ftp_passive'] ?? true));
            $this->ensureFtpDirectory($connection, $directory);

            if ($directory !== '/' && @ftp_chdir($connection, $directory) === false) {
                throw new RuntimeException(sprintf('Remote FTP directory "%s" is not accessible.', $directory));
            }

            if (@ftp_put($connection, $filename, $localPath, FTP_BINARY) === false) {
                throw new RuntimeException(sprintf('Failed to upload "%s" to the configured FTP disk.', $filename));
            }
        } finally {
            @ftp_close($connection);
        }

        return $this->normalizePath($path);
    }

    /**
     * Handles the upload sftp workflow.
     */
    private function uploadSftp(string $localPath, string $path): string
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is not available in this runtime.');
        }

        $remotePath = $this->remotePath($path);
        $handle = fopen($localPath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the upload source file.');
        }

        try {
            $this->ensureSftpDirectory(dirname($remotePath));

            $ch = curl_init(sprintf(
                'sftp://%s:%d%s',
                $this->requiredString('ftp_host'),
                $this->port(),
                $this->encodeRemotePath($remotePath)
            ));

            if ($ch === false) {
                throw new RuntimeException('Unable to initialise the SFTP client.');
            }

            curl_setopt_array($ch, [
                CURLOPT_USERPWD => $this->requiredString('ftp_username') . ':' . $this->requiredString('ftp_password'),
                CURLOPT_UPLOAD => true,
                CURLOPT_PROTOCOLS => CURLPROTO_SFTP,
                CURLOPT_READDATA => $handle,
                CURLOPT_INFILESIZE => filesize($localPath),
                CURLOPT_TIMEOUT => $this->timeout(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new RuntimeException('SFTP upload failed: ' . $error);
            }

            curl_close($ch);
        } finally {
            fclose($handle);
        }

        return $this->normalizePath($path);
    }

    /**
     * Handles the download ftp workflow.
     */
    private function downloadFtp(string $path): string
    {
        if (!function_exists('ftp_connect')) {
            throw new RuntimeException('The PHP FTP extension is not available in this runtime.');
        }

        $remotePath = $this->remotePath($path);
        $directory = dirname($remotePath);
        $filename = basename($remotePath);
        $temp = tempnam(sys_get_temp_dir(), 'catalyst-storage-read-');
        if ($temp === false) {
            throw new RuntimeException('Unable to create a temporary download file.');
        }

        $connection = $this->protocol() === 'ftps'
            ? @ftp_ssl_connect($this->requiredString('ftp_host'), $this->port(), $this->timeout())
            : @ftp_connect($this->requiredString('ftp_host'), $this->port(), $this->timeout());

        if ($connection === false) {
            @unlink($temp);
            throw new RuntimeException('Unable to connect to the configured FTP server.');
        }

        try {
            if (@ftp_login($connection, $this->requiredString('ftp_username'), $this->requiredString('ftp_password')) === false) {
                throw new RuntimeException('FTP authentication failed with the configured credentials.');
            }

            @ftp_pasv($connection, (bool) ($this->config['ftp_passive'] ?? true));

            if ($directory !== '/' && @ftp_chdir($connection, $directory) === false) {
                throw new RuntimeException('Remote FTP directory is not accessible.');
            }

            if (@ftp_get($connection, $temp, $filename, FTP_BINARY) === false) {
                throw new RuntimeException('Unable to read the requested FTP object.');
            }
        } finally {
            @ftp_close($connection);
        }

        try {
            $contents = @file_get_contents($temp);
            if ($contents === false) {
                throw new RuntimeException('Unable to read the downloaded FTP payload.');
            }

            return $contents;
        } finally {
            @unlink($temp);
        }
    }

    /**
     * Handles the download sftp workflow.
     */
    private function downloadSftp(string $path): string
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is not available in this runtime.');
        }

        $ch = curl_init(sprintf(
            'sftp://%s:%d%s',
            $this->requiredString('ftp_host'),
            $this->port(),
            $this->encodeRemotePath($this->remotePath($path))
        ));

        if ($ch === false) {
            throw new RuntimeException('Unable to initialise the SFTP client.');
        }

        curl_setopt_array($ch, [
            CURLOPT_USERPWD => $this->requiredString('ftp_username') . ':' . $this->requiredString('ftp_password'),
            CURLOPT_PROTOCOLS => CURLPROTO_SFTP,
            CURLOPT_TIMEOUT => $this->timeout(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('SFTP read failed: ' . $error);
        }

        curl_close($ch);

        return (string) $result;
    }

    /**
     * Handles the delete workflow.
     */
    private function deleteFtp(string $path): bool
    {
        if (!function_exists('ftp_connect')) {
            return false;
        }

        $remotePath = $this->remotePath($path);
        $directory = dirname($remotePath);
        $filename = basename($remotePath);
        $connection = $this->protocol() === 'ftps'
            ? @ftp_ssl_connect($this->requiredString('ftp_host'), $this->port(), $this->timeout())
            : @ftp_connect($this->requiredString('ftp_host'), $this->port(), $this->timeout());

        if ($connection === false) {
            return false;
        }

        try {
            if (@ftp_login($connection, $this->requiredString('ftp_username'), $this->requiredString('ftp_password')) === false) {
                return false;
            }

            @ftp_pasv($connection, (bool) ($this->config['ftp_passive'] ?? true));

            if ($directory !== '/' && @ftp_chdir($connection, $directory) === false) {
                return false;
            }

            return @ftp_delete($connection, $filename);
        } finally {
            @ftp_close($connection);
        }
    }

    /**
     * Handles the delete workflow.
     */
    private function deleteSftp(string $path): bool
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $remotePath = $this->remotePath($path);
        $directory = dirname($remotePath);
        $directoryUrl = sprintf(
            'sftp://%s:%d%s',
            $this->requiredString('ftp_host'),
            $this->port(),
            $this->encodeRemotePath($directory === '.' ? '/' : $directory)
        );

        $ch = curl_init($directoryUrl);
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_USERPWD => $this->requiredString('ftp_username') . ':' . $this->requiredString('ftp_password'),
            CURLOPT_PROTOCOLS => CURLPROTO_SFTP,
            CURLOPT_TIMEOUT => $this->timeout(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_QUOTE => ['rm ' . $remotePath],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return $result !== false && $error === '';
    }

    /**
     * Handles the exists ftp workflow.
     */
    private function existsFtp(string $path): bool
    {
        if (!function_exists('ftp_connect')) {
            return false;
        }

        $remotePath = $this->remotePath($path);
        $directory = dirname($remotePath);
        $filename = basename($remotePath);
        $connection = $this->protocol() === 'ftps'
            ? @ftp_ssl_connect($this->requiredString('ftp_host'), $this->port(), $this->timeout())
            : @ftp_connect($this->requiredString('ftp_host'), $this->port(), $this->timeout());

        if ($connection === false) {
            return false;
        }

        try {
            if (@ftp_login($connection, $this->requiredString('ftp_username'), $this->requiredString('ftp_password')) === false) {
                return false;
            }

            @ftp_pasv($connection, (bool) ($this->config['ftp_passive'] ?? true));

            if ($directory !== '/' && @ftp_chdir($connection, $directory) === false) {
                return false;
            }

            return @ftp_size($connection, $filename) !== -1;
        } finally {
            @ftp_close($connection);
        }
    }

    /**
     * Handles the exists sftp workflow.
     */
    private function existsSftp(string $path): bool
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $parent = dirname($this->remotePath($path));
        $filename = basename($this->remotePath($path));
        $ch = curl_init(sprintf(
            'sftp://%s:%d%s',
            $this->requiredString('ftp_host'),
            $this->port(),
            $this->encodeRemotePath($parent === '.' ? '/' : $parent)
        ));

        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_USERPWD => $this->requiredString('ftp_username') . ':' . $this->requiredString('ftp_password'),
            CURLOPT_PROTOCOLS => CURLPROTO_SFTP,
            CURLOPT_TIMEOUT => $this->timeout(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_DIRLISTONLY => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        return is_string($result) && str_contains($result, $filename);
    }

    /**
     * Handles the ensure ftp directory workflow.
     */
    private function ensureFtpDirectory(mixed $connection, string $directory): void
    {
        $segments = array_values(array_filter(explode('/', trim($directory, '/')), static fn(string $segment): bool => $segment !== ''));
        if ($segments === []) {
            return;
        }

        @ftp_chdir($connection, '/');
        $currentPath = '';

        foreach ($segments as $segment) {
            $currentPath .= '/' . $segment;

            if (@ftp_chdir($connection, $currentPath) !== false) {
                continue;
            }

            if (@ftp_mkdir($connection, $currentPath) === false) {
                throw new RuntimeException(sprintf('Unable to create remote FTP directory "%s".', $currentPath));
            }
        }
    }

    /**
     * Handles the ensure sftp directory workflow.
     */
    private function ensureSftpDirectory(string $directory): void
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is not available in this runtime.');
        }

        $segments = array_values(array_filter(explode('/', trim($directory, '/')), static fn(string $segment): bool => $segment !== ''));
        if ($segments === []) {
            return;
        }

        $commands = [];
        $current = '';

        foreach ($segments as $segment) {
            $current .= '/' . $segment;
            $commands[] = 'mkdir ' . $current;
        }

        $ch = curl_init(sprintf(
            'sftp://%s:%d/',
            $this->requiredString('ftp_host'),
            $this->port()
        ));

        if ($ch === false) {
            throw new RuntimeException('Unable to initialise the SFTP client.');
        }

        curl_setopt_array($ch, [
            CURLOPT_USERPWD => $this->requiredString('ftp_username') . ':' . $this->requiredString('ftp_password'),
            CURLOPT_PROTOCOLS => CURLPROTO_SFTP,
            CURLOPT_TIMEOUT => $this->timeout(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_QUOTE => $commands,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Handles the protocol workflow.
     */
    private function protocol(): string
    {
        $protocol = strtolower(trim((string) ($this->config['ftp_protocol'] ?? 'ftp')));

        if (!in_array($protocol, ['ftp', 'ftps', 'sftp'], true)) {
            throw new RuntimeException('Unsupported transfer protocol. Use FTP, FTPS or SFTP.');
        }

        return $protocol;
    }

    /**
     * Handles the port workflow.
     */
    private function port(): int
    {
        return (int) ($this->config['ftp_port'] ?? ($this->protocol() === 'sftp' ? 22 : 21));
    }

    /**
     * Handles the timeout workflow.
     */
    private function timeout(): int
    {
        return (int) ($this->config['ftp_timeout'] ?? 10);
    }

    /**
     * Handles the remote path workflow.
     */
    private function remotePath(string $path): string
    {
        $normalized = $this->normalizePath($path);
        $root = $this->normalizeRoot((string) ($this->config['ftp_root'] ?? '/'));

        return $root === '/'
            ? '/' . $normalized
            : $root . '/' . $normalized;
    }

    /**
     * Normalizes the provided value.
     */
    private function normalizePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        $segments = array_values(array_filter(explode('/', $path), static fn(string $segment): bool => $segment !== ''));

        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..') {
                throw new RuntimeException('Unsafe storage path segment detected.');
            }
        }

        if ($segments === []) {
            throw new RuntimeException('Storage path cannot be empty.');
        }

        return implode('/', $segments);
    }

    /**
     * Normalizes the provided value.
     */
    private function normalizeRoot(string $root): string
    {
        $trimmed = trim($root);

        if ($trimmed === '') {
            return '/';
        }

        $normalized = '/' . ltrim(str_replace('\\', '/', $trimmed), '/');

        return rtrim($normalized, '/') ?: '/';
    }

    /**
     * Handles the encode remote path workflow.
     */
    private function encodeRemotePath(string $path): string
    {
        $segments = explode('/', ltrim($path, '/'));
        $encoded = array_map(
            static fn(string $segment): string => rawurlencode($segment),
            array_filter($segments, static fn(string $segment): bool => $segment !== '')
        );

        return '/' . implode('/', $encoded);
    }

    /**
     * Handles the required string workflow.
     */
    private function requiredString(string $key): string
    {
        $value = trim((string) ($this->config[$key] ?? ''));

        if ($value === '') {
            throw new RuntimeException(sprintf('Storage disk configuration is missing "%s".', $key));
        }

        return $value;
    }
}
