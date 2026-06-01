<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst\Framework\Mail
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 **************************************************************************************/

namespace Catalyst\Framework\Mail;

use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;

/**************************************************************************************
 * DkimGenerator — RSA DKIM key pair generator for mail authentication.
 *
 * Generates 2048-bit RSA key pairs suitable for DKIM signing and stores them
 * under boot-core/config/dkim/{domain}/{connectionId}/.
 *
 * Usage:
 *   $result = DkimGenerator::getInstance()->generateKeys('example.com', 'mail', 'mail1');
 *   // $result['dnsRecord'] → TXT record to add to DNS
 *   // $result['privateKeyPath'] → path for PHPMailer DKIM config
 *
 * @package Catalyst\Framework\Mail
 **************************************************************************************/
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
     * Generate an RSA 2048-bit key pair and persist it to disk.
     *
     * @param string $domain       Domain name (e.g. "example.com")
     * @param string $selector     DKIM selector (e.g. "mail", "dkim2025")
     * @param string $connectionId Mail connection identifier (e.g. "mail1")
     * @return array{
     *     selector: string,
     *     domain: string,
     *     privateKeyPath: string,
     *     publicKeyPath: string,
     *     dnsRecord: string,
     *     storageDir: string
     * }
     * @throws RuntimeException when OpenSSL is unavailable or key generation fails
     */
    public function generateKeys(string $domain, string $selector, string $connectionId = 'mail1'): array
    {
        $this->domain     = strtolower(trim($domain));
        $this->selector   = strtolower(trim($selector));
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
     * Resolve the absolute storage path for this domain/connection pair.
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
     * Create the storage directory recursively if it does not exist.
     *
     * @throws RuntimeException on failure
     */
    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0750, true)) {
            throw new RuntimeException("DkimGenerator: cannot create directory '{$path}'");
        }
    }

    /**
     * Generate RSA 2048-bit key pair using OpenSSL.
     *
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
     * Write private.pem and public.pem to the storage directory.
     *
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
     * Build the DNS TXT record string for this key pair.
     *
     * Format: {selector}._domainkey.{domain} IN TXT "v=DKIM1; k=rsa; p={base64pubkey}"
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
