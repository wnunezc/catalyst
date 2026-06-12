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

namespace Catalyst\Framework\Mail;

use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;

/**
 * RSA DKIM key-pair generator for mail authentication.
 *
 * Generates and persists DKIM private/public keys and returns the DNS TXT
 * record payload needed by the configured mail connection.
 *
 * @package Catalyst\Framework\Mail
 * Responsibility: Generate DKIM key material and DNS records for mail signing.
 */
class DkimGenerator
{
    use SingletonTrait;

    private string $domain     = '';
    private string $selector   = '';
    private string $storageDir = '';
    private string $privateKey = '';
    private string $publicKey  = '';

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Generate an RSA key pair, persist it and return DKIM configuration data. selector: string, domain: string, privateKeyPath: string, publicKeyPath: string, dnsRecord: string, storageDir: string }.
     *
     * Responsibility: Generate an RSA key pair, persist it and return DKIM configuration data. selector: string, domain: string, privateKeyPath: string, publicKeyPath: string, dnsRecord: string, storageDir: string }.
     * @param string $domain       Domain name for the DKIM identity
     * @param string $selector     DKIM selector for the key pair
     * @param string $connectionId Mail connection identifier
     * @return array{
     * @throws RuntimeException when OpenSSL is unavailable or key generation fails
     */
    public function generateKeys(string $domain, string $selector, string $connectionId = 'mail1'): array
    {
        $this->domain     = strtolower(trim($domain));
        $this->selector   = strtolower(trim($selector));
        $connectionId     = strtolower(trim($connectionId));

        if (filter_var($this->domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            throw new RuntimeException('DkimGenerator: invalid DKIM domain');
        }

        if (preg_match('/^[a-z0-9](?:[a-z0-9_-]{0,61}[a-z0-9])?$/', $this->selector) !== 1) {
            throw new RuntimeException('DkimGenerator: invalid DKIM selector');
        }

        if (preg_match('/^[a-z0-9](?:[a-z0-9_-]{0,62}[a-z0-9])?$/', $connectionId) !== 1) {
            throw new RuntimeException('DkimGenerator: invalid mail connection identifier');
        }

        $this->storageDir = $this->resolveStorageDir($this->domain, $connectionId);

        $this->ensureDirectory($this->storageDir);
        $this->generateRsaKeyPair();
        $this->persistKeys();

        return [
            'selector'       => $this->selector,
            'domain'         => $this->domain,
            'privateKeyPath' => $this->storageDir . DIRECTORY_SEPARATOR . 'private.pem',
            'publicKeyPath'  => $this->storageDir . DIRECTORY_SEPARATOR . 'public.pem',
            'dnsRecord'      => $this->buildDnsRecord(),
            'storageDir'     => $this->storageDir,
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve the DKIM storage directory for a domain and connection.
     *
     * Responsibility: Resolve the DKIM storage directory for a domain and connection.
     */
    private function resolveStorageDir(string $domain, string $connectionId): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            PD,
            'boot-core',
            'config',
            'dkim',
            $domain,
            $connectionId,
        ]);
    }

    /**
     * Ensure the DKIM storage directory exists.
     *
     * Responsibility: Ensure the DKIM storage directory exists.
     * @throws RuntimeException on failure
     */
    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0750, true)) {
            throw new RuntimeException("DkimGenerator: cannot create directory '{$path}'");
        }
    }

    /**
     * Generate the in-memory RSA key pair through OpenSSL.
     *
     * Responsibility: Generate the in-memory RSA key pair through OpenSSL.
     * @throws RuntimeException when OpenSSL extension is absent or generation fails
     */
    private function generateRsaKeyPair(): void
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('DkimGenerator: OpenSSL extension is required');
        }

        $config = [
            'digest_alg'       => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);

        if ($resource === false) {
            throw new RuntimeException(
                'DkimGenerator: key generation failed — ' . openssl_error_string()
            );
        }

        if (!openssl_pkey_export($resource, $privateKeyPem)) {
            throw new RuntimeException(
                'DkimGenerator: private key export failed — ' . openssl_error_string()
            );
        }

        $details = openssl_pkey_get_details($resource);

        if ($details === false) {
            throw new RuntimeException(
                'DkimGenerator: cannot read key details — ' . openssl_error_string()
            );
        }

        $this->privateKey = $privateKeyPem;
        $this->publicKey  = $details['key'];
    }

    /**
     * Persist generated private and public keys to disk.
     *
     * Responsibility: Persist generated private and public keys to disk.
     * @throws RuntimeException on write failure
     */
    private function persistKeys(): void
    {
        $privatePath = $this->storageDir . DIRECTORY_SEPARATOR . 'private.pem';
        $publicPath  = $this->storageDir . DIRECTORY_SEPARATOR . 'public.pem';

        if (file_put_contents($privatePath, $this->privateKey) === false) {
            throw new RuntimeException("DkimGenerator: cannot write private key to '{$privatePath}'");
        }

        if (file_put_contents($publicPath, $this->publicKey) === false) {
            throw new RuntimeException("DkimGenerator: cannot write public key to '{$publicPath}'");
        }

        // Best-effort permission lockdown. Silently ignored on volumes that do
        // not support chmod (e.g. Docker bind mounts on Windows hosts), where it
        // would otherwise emit a warning that corrupts the JSON response.
        @chmod($privatePath, 0640);
    }

    /**
     * Build the DNS TXT record string for the generated public key.
     *
     * Responsibility: Build the DNS TXT record string for the generated public key.
     */
    private function buildDnsRecord(): string
    {
        $pubKeyStripped = preg_replace(
            '/-----(?:BEGIN|END) PUBLIC KEY-----/',
            '',
            $this->publicKey
        );
        $pubKeyBase64 = trim(str_replace(["\r", "\n", ' '], '', (string)$pubKeyStripped));

        $host  = "{$this->selector}._domainkey.{$this->domain}";
        $value = "v=DKIM1; k=rsa; p={$pubKeyBase64}";

        return "{$host} IN TXT \"{$value}\"";
    }
}
