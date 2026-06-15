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

namespace Catalyst\Helpers\Security;

use Random\RandomException;
use RuntimeException;

/**
 * CspNonce — per-request Content Security Policy nonce.
 *
 * Generates a cryptographically random nonce once per request.
 * The SecurityHeadersMiddleware calls generate() before the response
 * is built, and then uses get() in the CSP header value.
 * View templates use get() to add the nonce attribute to inline scripts.
 *
 * @package Catalyst\Helpers\Security
 * Responsibility: Generates and exposes the nonce shared by CSP headers and inline scripts.
 */
class CspNonce
{
    private static string $nonce = '';

    /**
     * Generate a fresh nonce for the current request.
     * Idempotent — calling twice returns the same nonce.
     */
    public static function generate(): void
    {
        if (self::$nonce !== '') {
            return;
        }

        try {
            self::$nonce = base64_encode(random_bytes(16));
        } catch (RandomException $exception) {
            throw new RuntimeException(
                'Unable to generate a cryptographically secure CSP nonce.',
                previous: $exception
            );
        }
    }

    /**
     * Return the current nonce, generating one if not yet created.
     */
    public static function get(): string
    {
        if (self::$nonce === '') {
            self::generate();
        }

        return self::$nonce;
    }
}
