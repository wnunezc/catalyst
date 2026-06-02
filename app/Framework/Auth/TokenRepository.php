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

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Stores email-verification and password-reset tokens as one-time hashes.
 *
 * @package Catalyst\Framework\Auth
 * Responsibility: Create, consume and invalidate user recovery tokens without persisting raw token values.
 */
class TokenRepository
{
    use SingletonTrait;

    /** Token valid for 1 hour */
    private const TTL_SECONDS = 3600;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes database and logging collaborators for token storage.
     *
     * Responsibility: Initializes database and logging collaborators for token storage.
     */
    protected function __construct()
    {
        $this->db     = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    // -------------------------------------------------------------------------
    // Email verification tokens
    // -------------------------------------------------------------------------

    /**
     * Create a new email-verification token for a user. Invalidates any existing active tokens for that user first.
     *
     * Responsibility: Create a new email-verification token for a user. Invalidates any existing active tokens for that user first.
     * @param int $userId
     * @return string  Raw token to embed in the email link
     */
    public function createVerificationToken(int $userId): string
    {
        $this->invalidatePrevious('email_verification_tokens', $userId);

        $raw  = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);

        $this->db->table('email_verification_tokens')->insert([
            'user_id'    => $userId,
            'token_hash' => $hash,
            'active'     => 1,
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL_SECONDS),
        ]);

        return $raw;
    }

    /**
     * Consume an email-verification token. Returns the user_id on success, null if invalid/expired/already used.
     *
     * Responsibility: Consume an email-verification token. Returns the user_id on success, null if invalid/expired/already used.
     * @param string $rawToken
     * @return int|null
     */
    public function consumeVerificationToken(string $rawToken): ?int
    {
        return $this->consumeToken('email_verification_tokens', $rawToken);
    }

    // -------------------------------------------------------------------------
    // Password-reset tokens
    // -------------------------------------------------------------------------

    /**
     * Create a new password-reset token for a user. Invalidates any existing active tokens for that user first.
     *
     * Responsibility: Create a new password-reset token for a user. Invalidates any existing active tokens for that user first.
     * @param int $userId
     * @return string  Raw token to embed in the email link
     */
    public function createPasswordResetToken(int $userId): string
    {
        $this->invalidatePrevious('password_reset_tokens', $userId);

        $raw  = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);

        $this->db->table('password_reset_tokens')->insert([
            'user_id'    => $userId,
            'token_hash' => $hash,
            'active'     => 1,
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL_SECONDS),
        ]);

        return $raw;
    }

    /**
     * Consume a password-reset token. Returns the user_id on success, null if invalid/expired/already used.
     *
     * Responsibility: Consume a password-reset token. Returns the user_id on success, null if invalid/expired/already used.
     * @param string $rawToken
     * @return int|null
     */
    public function consumePasswordResetToken(string $rawToken): ?int
    {
        return $this->consumeToken('password_reset_tokens', $rawToken);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Invalidate all active tokens for a user in the given table.
     *
     * Responsibility: Invalidate all active tokens for a user in the given table.
     * @param string $table
     * @param int    $userId
     * @return void
     */
    private function invalidatePrevious(string $table, int $userId): void
    {
        try {
            $this->db
                ->table($table)
                ->whereEqual('user_id', $userId)
                ->whereEqual('active', 1)
                ->update(['active' => 0]);
        } catch (Exception $e) {
            $this->logger->error("TokenRepository::invalidatePrevious({$table}) failed", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate a raw token against the given table and mark it as used (active=0).
     *
     * Responsibility: Validate a raw token against the given table and mark it as used (active=0).
     * @param string $table
     * @param string $rawToken
     * @return int|null  user_id on success
     */
    private function consumeToken(string $table, string $rawToken): ?int
    {
        try {
            $hash = hash('sha256', $rawToken);

            $row = $this->db
                ->table($table)
                ->whereEqual('token_hash', $hash)
                ->whereEqual('active', 1)
                ->where('expires_at', '>', date('Y-m-d H:i:s'))
                ->first();

            if (!$row) {
                return null;
            }

            // Mark as used — never delete
            $this->db
                ->table($table)
                ->whereEqual('id', (int)$row['id'])
                ->update(['active' => 0]);

            return (int)$row['user_id'];
        } catch (Exception $e) {
            $this->logger->error("TokenRepository::consumeToken({$table}) failed", [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
