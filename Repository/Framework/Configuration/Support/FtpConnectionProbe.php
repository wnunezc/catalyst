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

namespace Catalyst\Repository\Configuration\Support;

use RuntimeException;

/**
 * Verifies remote transfer connectivity by uploading and removing a temporary payload.
 *
 * @package Catalyst\Repository\Configuration\Support
 * Responsibility: Runs FTP, FTPS or SFTP upload-cleanup pretests and reports cleanup warnings.
 */
final class FtpConnectionProbe
{
    /**
     * Dispatches a transfer pretest to the configured protocol.
     *
     * Responsibility: Dispatches a transfer pretest to the configured protocol.
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function pretest(array $config): array
    {
        $protocol = strtolower(trim((string) ($config['ftp_protocol'] ?? 'ftp')));

        return match ($protocol) {
            'ftp', 'ftps' => $this->probeFtp($config, $protocol),
            'sftp' => $this->probeSftp($config),
            default => throw new RuntimeException('Unsupported transfer protocol. Use FTP, FTPS or SFTP.'),
        };
    }

    /**
     * Uploads and removes a temporary file over FTP or FTPS.
     *
     * Responsibility: Uploads and removes a temporary file over FTP or FTPS.
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function probeFtp(array $config, string $protocol): array
    {
        if (!function_exists('ftp_connect')) {
            throw new RuntimeException('The PHP FTP extension is not available in this runtime.');
        }

        if ($protocol === 'ftps' && !function_exists('ftp_ssl_connect')) {
            throw new RuntimeException('The PHP FTP SSL extension is not available in this runtime.');
        }

        $host = trim((string) ($config['ftp_host'] ?? ''));
        $port = (int) ($config['ftp_port'] ?? ($protocol === 'ftps' ? 21 : 21));
        $timeout = (int) ($config['ftp_timeout'] ?? 10);
        $username = trim((string) ($config['ftp_username'] ?? ''));
        $password = (string) ($config['ftp_password'] ?? '');
        $root = $this->normalizeRoot((string) ($config['ftp_root'] ?? '/'));
        $passive = (bool) ($config['ftp_passive'] ?? true);
        $remoteFile = $this->buildRemotePath($root);
        $uploadTarget = basename($remoteFile);
        $localFile = $this->createTempPayload($protocol);

        $connection = $protocol === 'ftps'
            ? @ftp_ssl_connect($host, $port, $timeout)
            : @ftp_connect($host, $port, $timeout);

        if ($connection === false) {
            @unlink($localFile);
            throw new RuntimeException(sprintf('Could not connect to %s:%d over %s.', $host, $port, strtoupper($protocol)));
        }

        try {
            @ftp_set_option($connection, FTP_TIMEOUT_SEC, max(1, $timeout));

            if (@ftp_login($connection, $username, $password) === false) {
                throw new RuntimeException('FTP authentication failed with the supplied credentials.');
            }

            if (@ftp_pasv($connection, $passive) === false) {
                throw new RuntimeException('FTP passive mode could not be configured on the current connection.');
            }

            if ($root !== '/' && @ftp_chdir($connection, $root) === false) {
                throw new RuntimeException(sprintf('Remote root "%s" does not exist or is not accessible.', $root));
            }

            if (@ftp_put($connection, $uploadTarget, $localFile, FTP_BINARY) === false) {
                throw new RuntimeException(sprintf('FTP upload pretest failed for "%s".', $remoteFile));
            }

            $deleted = @ftp_delete($connection, $uploadTarget);

            return [
                'protocol' => $protocol,
                'host' => $host,
                'port' => $port,
                'remote_root' => $root,
                'remote_file' => $remoteFile,
                'upload_ok' => true,
                'delete_ok' => $deleted === true,
                'cleanup_warning' => $deleted ? null : sprintf('Uploaded test file could not be deleted: %s', $remoteFile),
            ];
        } finally {
            @unlink($localFile);
            @ftp_close($connection);
        }
    }

    /**
     * Uploads and removes a temporary file over SFTP.
     *
     * Responsibility: Uploads and removes a temporary file over SFTP.
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function probeSftp(array $config): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is not available in this runtime.');
        }

        $host = trim((string) ($config['ftp_host'] ?? ''));
        $port = (int) ($config['ftp_port'] ?? 22);
        $timeout = (int) ($config['ftp_timeout'] ?? 10);
        $username = trim((string) ($config['ftp_username'] ?? ''));
        $password = (string) ($config['ftp_password'] ?? '');
        $root = $this->normalizeRoot((string) ($config['ftp_root'] ?? '/'));
        $remoteFile = $this->buildRemotePath($root);
        $localFile = $this->createTempPayload('sftp');
        $remoteUrl = sprintf(
            'sftp://%s:%d%s',
            $host,
            $port,
            $this->encodeRemotePath($remoteFile)
        );

        $handle = fopen($localFile, 'rb');

        if ($handle === false) {
            @unlink($localFile);
            throw new RuntimeException('Could not open the temporary upload payload.');
        }

        try {
            $ch = curl_init($remoteUrl);

            if ($ch === false) {
                throw new RuntimeException('Could not initialise the SFTP probe client.');
            }

            curl_setopt_array($ch, [
                CURLOPT_USERPWD => $username . ':' . $password,
                CURLOPT_UPLOAD => true,
                CURLOPT_PROTOCOLS => CURLPROTO_SFTP,
                CURLOPT_READDATA => $handle,
                CURLOPT_INFILESIZE => filesize($localFile),
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => false,
            ]);

            $result = curl_exec($ch);

            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new RuntimeException('SFTP upload pretest failed: ' . $error);
            }

            curl_close($ch);

            $deleted = $this->deleteSftpFile(
                $host,
                $port,
                $username,
                $password,
                $timeout,
                $remoteFile
            );

            return [
                'protocol' => 'sftp',
                'host' => $host,
                'port' => $port,
                'remote_root' => $root,
                'remote_file' => $remoteFile,
                'upload_ok' => true,
                'delete_ok' => $deleted,
                'cleanup_warning' => $deleted ? null : sprintf('Uploaded test file could not be deleted: %s', $remoteFile),
            ];
        } finally {
            fclose($handle);
            @unlink($localFile);
        }
    }

    /**
     * Attempts to remove an uploaded SFTP pretest file.
     *
     * Responsibility: Attempts to remove an uploaded SFTP pretest file.
     */
    private function deleteSftpFile(
        string $host,
        int $port,
        string $username,
        string $password,
        int $timeout,
        string $remoteFile
    ): bool {
        $directory = dirname($remoteFile);
        $directoryUrl = sprintf(
            'sftp://%s:%d%s',
            $host,
            $port,
            $this->encodeRemotePath($directory === '.' ? '/' : $directory)
        );

        $ch = curl_init($directoryUrl);

        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_USERPWD => $username . ':' . $password,
            CURLOPT_PROTOCOLS => CURLPROTO_SFTP,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_QUOTE => ['rm ' . $remoteFile],
        ]);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return $result !== false && $error === '';
    }

    /**
     * Normalizes a remote root as an absolute slash-delimited path.
     *
     * Responsibility: Normalizes a remote root as an absolute slash-delimited path.
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
     * Builds a unique remote path for the temporary pretest file.
     *
     * Responsibility: Builds a unique remote path for the temporary pretest file.
     */
    private function buildRemotePath(string $root): string
    {
        $filename = '.catalyst-pretest-' . bin2hex(random_bytes(6)) . '.txt';

        return $root === '/'
            ? '/' . $filename
            : $root . '/' . $filename;
    }

    /**
     * Creates a local temporary payload for a transfer pretest.
     *
     * Responsibility: Creates a local temporary payload for a transfer pretest.
     */
    private function createTempPayload(string $protocol): string
    {
        $file = tempnam(sys_get_temp_dir(), 'catalyst-ftp-');

        if ($file === false) {
            throw new RuntimeException('Could not create the temporary upload payload.');
        }

        $payload = sprintf(
            "Catalyst transfer pretest\nprotocol=%s\ntimestamp=%s\n",
            strtoupper($protocol),
            gmdate(DATE_ATOM)
        );

        if (file_put_contents($file, $payload) === false) {
            @unlink($file);
            throw new RuntimeException('Could not write the temporary upload payload.');
        }

        return $file;
    }

    /**
     * Encodes each segment of a remote path for use in an SFTP URL.
     *
     * Responsibility: Encodes each segment of a remote path for use in an SFTP URL.
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
}
