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

namespace Catalyst\Framework\Attachment;

use RuntimeException;

/**
 * Signs and verifies compact attachment verification tokens.
 *
 * @package Catalyst\Framework\Attachment
 * Responsibility: Creates tamper-evident payloads suitable for QR verification URLs.
 */
final class AttachmentVerificationSigner
{
    /**
     * Creates a signer with a private application secret.
     *
     * Responsibility: Keeps signing key material scoped to token generation and verification operations.
     */
    public function __construct(private readonly string $secret)
    {
        if (trim($secret) === '') {
            throw new RuntimeException('A verification signing secret is required.');
        }
    }

    /**
     * Signs a verification payload and returns a compact token.
     *
     * Responsibility: Produces tamper-evident attachment verification tokens without exposing raw application secrets.
     * @param array<string, mixed> $payload
     */
    public function sign(array $payload): string
    {
        $payload['issued_at'] = $payload['issued_at'] ?? time();
        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $body, $this->secret, true);

        return $body . '.' . $this->base64UrlEncode($signature);
    }

    /**
     * Verifies a compact token and returns its payload.
     *
     * Responsibility: Rejects malformed, expired or tampered verification tokens before returning trusted payload data.
     * @return array<string, mixed>|null
     */
    public function verify(string $token, ?int $now = null, ?callable $revocationChecker = null): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$body, $signature] = $parts;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $body, $this->secret, true));
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($body), true);
        if (!is_array($payload)) {
            return null;
        }

        $expiresAt = (int) ($payload['expires_at'] ?? 0);
        if ($expiresAt > 0 && ($now ?? time()) > $expiresAt) {
            return null;
        }

        if ($revocationChecker !== null && (bool) $revocationChecker($payload)) {
            return null;
        }

        return $payload;
    }

    /**
     * Builds the URL payload that an app may encode into a QR image.
     *
     * Responsibility: Formats signed attachment verification links while leaving QR rendering to the consuming application.
     * @param array<string, mixed> $payload
     */
    public function verificationUrl(string $baseUrl, array $payload): string
    {
        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return rtrim($baseUrl) . $separator . 'token=' . rawurlencode($this->sign($payload));
    }

    /**
     * Encodes binary data using base64url without padding.
     *
     * Responsibility: Provides deterministic token-safe encoding for signature segments.
     */
    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * Decodes base64url data without padding.
     *
     * Responsibility: Restores token segments to binary form for signature verification.
     */
    private function base64UrlDecode(string $value): string
    {
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($value, '-_', '+/'), true) ?: '';
    }
}