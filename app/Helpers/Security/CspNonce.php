<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * @package   Catalyst\Helpers\Security
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @link      https://catalyst.dock Local development URL
 */

namespace Catalyst\Helpers\Security;

use Random\RandomException;

/**
 * CspNonce — per-request Content Security Policy nonce.
 *
 * Generates a cryptographically random nonce once per request.
 * The SecurityHeadersMiddleware calls generate() before the response
 * is built, and then uses get() in the CSP header value.
 * View templates use get() to add the nonce attribute to inline scripts.
 *
 * @package Catalyst\Helpers\Security
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
        } catch (RandomException) {
            // Fallback: uniqid with entropy — not ideal but never fails
            self::$nonce = base64_encode(uniqid('', true) . mt_rand());
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
