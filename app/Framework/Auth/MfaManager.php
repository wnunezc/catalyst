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

namespace Catalyst\Framework\Auth;

use Catalyst\Framework\Traits\SingletonTrait;

/**************************************************************************************
 * MfaManager — TOTP secret generation, QR URI, code verification, backup codes.
 *
 * No external libraries. Uses only:
 *   - random_bytes()  — cryptographically secure secret generation
 *   - hash_hmac()     — HMAC-SHA1 per RFC 4226
 *   - hash_equals()   — timing-safe comparison for backup codes
 *
 * @package Catalyst\Framework\Auth
 */
/**
 * Defines the Mfa Manager class contract.
 *
 * @package Catalyst\Framework\Auth
 * Responsibility: Coordinates the mfa manager behavior within its module boundary.
 */
class MfaManager
{
    use SingletonTrait;

    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const TOTP_PERIOD     = 30;
    private const TOTP_DIGITS     = 6;
    private const TOTP_ALGORITHM  = 'sha1';
    private const SECRET_BYTES    = 20;   // 160-bit secret → 32 base32 chars

    // -------------------------------------------------------------------------
    // Secret generation
    // -------------------------------------------------------------------------

    /**
     * Generate a cryptographically random base32 TOTP secret.
     * 20 bytes of entropy → 32-character base32 string.
     *
     * @return string
     */
    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(self::SECRET_BYTES));
    }

    // -------------------------------------------------------------------------
    // QR provisioning URI
    // -------------------------------------------------------------------------

    /**
     * Build the otpauth:// URI used by authenticator apps (Google Authenticator, Aegis, etc.).
     *
     * @param string $secret  Base32 secret
     * @param string $email   User email (account label)
     * @param string $issuer  App / issuer name shown in the authenticator
     * @return string         otpauth://totp/... URI
     */
    public function generateQrUri(string $secret, string $email, string $issuer = 'Catalyst'): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            rawurlencode($issuer),
            rawurlencode($email),
            rawurlencode($secret),
            rawurlencode($issuer),
            self::TOTP_DIGITS,
            self::TOTP_PERIOD
        );
    }

    // -------------------------------------------------------------------------
    // Code verification
    // -------------------------------------------------------------------------

    /**
     * Verify a 6-digit TOTP code with a ±window step tolerance.
     *
     * Default window=1 allows codes from the previous and next 30-second windows,
     * accommodating minor clock drift between client and server.
     *
     * @param string $secret  Base32 TOTP secret
     * @param string $code    6-digit code supplied by the user
     * @param int    $window  Time-step tolerance in each direction (default 1)
     * @return bool
     */
    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $normalizedCode = $this->normalizeTotpCode($code);
        if ($normalizedCode === null) {
            return false;
        }

        $key       = $this->base32Decode($secret);
        $timestamp = (int)floor(time() / self::TOTP_PERIOD);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if ($this->computeTotp($key, $timestamp + $offset) === $normalizedCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalizes the provided value.
     */
    public function normalizeTotpCode(string $code): ?string
    {
        $normalized = preg_replace('/[\s-]+/', '', trim($code));

        if (!is_string($normalized) || strlen($normalized) !== self::TOTP_DIGITS || !ctype_digit($normalized)) {
            return null;
        }

        return $normalized;
    }

    /**
     * Normalizes the provided value.
     */
    public function normalizeBackupCode(string $code): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', trim($code)));
    }

    // -------------------------------------------------------------------------
    // Backup codes
    // -------------------------------------------------------------------------

    /**
     * Generate $count one-time backup codes.
     * Format: XXXX-XXXX (4 uppercase hex + dash + 4 uppercase hex).
     *
     * @param int $count Number of codes to generate (default 8)
     * @return string[]
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $hex    = strtoupper(bin2hex(random_bytes(4)));
            $codes[] = substr($hex, 0, 4) . '-' . substr($hex, 4, 4);
        }
        return $codes;
    }

    /**
     * Hash backup codes before persistence so DB rows never store them in clear text.
     *
     * @param string[] $codes
     * @return string[]
     */
    public function hashBackupCodes(array $codes): array
    {
        $hashed = [];

        foreach ($codes as $code) {
            $hashed[] = $this->hashBackupCode($code);
        }

        return $hashed;
    }

    /**
     * Verify a backup code against the stored list.
     * Removes the matching code on success (one-time use).
     *
     * The $codes array is modified in-place; the caller must persist the updated list.
     *
     * @param string   $code   Code supplied by the user (dashes optional, case-insensitive)
     * @param string[] &$codes Remaining backup codes (modified on success)
     * @return bool
     */
    public function verifyBackupCode(string $code, array &$codes): bool
    {
        $input = $this->normalizeBackupCode($code);
        $inputHash = hash('sha256', $input);

        foreach ($codes as $index => $stored) {
            if ($this->isHashedBackupCode($stored)) {
                if (!hash_equals(strtolower($stored), $inputHash)) {
                    continue;
                }
            } else {
                $storedNorm = $this->normalizeBackupCode($stored);
                if (!hash_equals($storedNorm, $input)) {
                    continue;
                }
            }

            array_splice($codes, $index, 1);
            return true;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // TOTP internals (RFC 4226 / RFC 6238)
    // -------------------------------------------------------------------------

    /**
     * Compute the TOTP code for a given binary key and counter value.
     *
     * Algorithm:
     *   1. Pack counter as 8-byte big-endian unsigned integer
     *   2. HMAC-SHA1(key, counter_bytes)
     *   3. Dynamic truncation → 31-bit integer
     *   4. Modulo 10^DIGITS → zero-pad to DIGITS
     *
     * @param string $key     Raw binary TOTP key
     * @param int    $counter Time step counter
     * @return string         Zero-padded DIGITS-character numeric string
     */
    private function computeTotp(string $key, int $counter): string
    {
        // 8-byte big-endian counter (high 32 bits are always 0 for Unix timestamps / 30)
        $counterBytes = pack('N*', 0) . pack('N*', $counter);

        $hash   = hash_hmac(self::TOTP_ALGORITHM, $counterBytes, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;

        $code = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
            ( ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::TOTP_DIGITS);

        return str_pad((string)$code, self::TOTP_DIGITS, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------------------------
    // Base32 codec
    // -------------------------------------------------------------------------

    /**
     * Encode a binary string to base32 (RFC 4648, no padding).
     *
     * @param string $data Raw binary input
     * @return string      Uppercase base32 string
     */
    private function base32Encode(string $data): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $result   = '';
        $buffer   = 0;
        $bitsLeft = 0;

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $buffer    = ($buffer << 8) | ord($data[$i]);
            $bitsLeft += 8;
            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $result   .= $alphabet[($buffer >> $bitsLeft) & 31];
            }
        }

        if ($bitsLeft > 0) {
            $result .= $alphabet[($buffer << (5 - $bitsLeft)) & 31];
        }

        return $result;
    }

    /**
     * Decode a base32 string to binary.
     * Silently skips invalid characters and strips '=' padding.
     *
     * @param string $data Base32 encoded string
     * @return string      Raw binary output
     */
    private function base32Decode(string $data): string
    {
        $data   = strtoupper(rtrim($data, '='));
        $map    = array_flip(str_split(self::BASE32_ALPHABET));
        $result = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $char = $data[$i];
            if (!isset($map[$char])) {
                continue;
            }
            $buffer    = ($buffer << 5) | $map[$char];
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result   .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $result;
    }

    /**
     * Handles the hash backup code workflow.
     */
    private function hashBackupCode(string $code): string
    {
        return hash('sha256', $this->normalizeBackupCode($code));
    }

    /**
     * Determines whether is Hashed Backup Code.
     */
    private function isHashedBackupCode(string $value): bool
    {
        return preg_match('/^[a-f0-9]{64}$/', $value) === 1;
    }
}
