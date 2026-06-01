<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework\Auth
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * RememberMe — persistent login via secure HTTP-only cookie and hashed token.
 *
 */

namespace Catalyst\Framework\Auth;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**************************************************************************************
 * RememberMe — persistent login token management
 *
 * Generates a cryptographically random token, stores its SHA-256 hash in
 * `remember_tokens`, and sets an HTTP-only cookie on the client.
 * Tokens are never physically deleted — they are invalidated via active=0.
 *
 * @package Catalyst\Framework\Auth
 */
class RememberMe
{
    use SingletonTrait;

    private const COOKIE_NAME = 'catalyst_remember';
    private const COOKIE_DAYS = 30;

    /**
     * @var DatabaseManager
     */
    private DatabaseManager $db;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->db     = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Create a remember-me token for a user and set the cookie.
     *
     * @param int $userId
     * @return void
     */
    public function create(int $userId): void
    {
        try {
            $token    = bin2hex(random_bytes(32));
            $hash     = hash('sha256', $token);
            $expires  = time() + (self::COOKIE_DAYS * 24 * 60 * 60);
            $isSecure = $this->isSecureRequest();

            $this->db->table('remember_tokens')->insert([
                'user_id'    => $userId,
                'token_hash' => $hash,
                'active'     => 1,
                'expires_at' => date('Y-m-d H:i:s', $expires),
            ]);

            setcookie(
                self::COOKIE_NAME,
                $token,
                [
                    'expires'  => $expires,
                    'path'     => '/',
                    'secure'   => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]
            );
        } catch (Exception $e) {
            $this->logger->error('RememberMe::create failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Resolve a remember-me cookie to a user ID.
     * Returns the user_id if the token is active and not expired, otherwise null.
     *
     * @return int|null
     */
    public function resolve(): ?int
    {
        $token = $_COOKIE[self::COOKIE_NAME] ?? null;

        if ($token === null || $token === '') {
            return null;
        }

        try {
            $hash = hash('sha256', $token);

            $row = $this->db
                ->table('remember_tokens')
                ->whereEqual('token_hash', $hash)
                ->whereEqual('active', 1)
                ->where('expires_at', '>', date('Y-m-d H:i:s'))
                ->first();

            return $row ? (int)$row['user_id'] : null;
        } catch (Exception $e) {
            $this->logger->error('RememberMe::resolve failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Invalidate all active remember-me tokens for a user.
     * Sets active=0 — never physically deletes rows.
     *
     * @param int $userId
     * @return void
     */
    public function invalidate(int $userId): void
    {
        try {
            $this->db
                ->table('remember_tokens')
                ->whereEqual('user_id', $userId)
                ->whereEqual('active', 1)
                ->update(['active' => 0]);
        } catch (Exception $e) {
            $this->logger->error('RememberMe::invalidate failed', ['error' => $e->getMessage()]);
        }

        // Clear the cookie regardless of DB result
        $this->clearCookie();
    }

    /**
     * Check whether a remember-me cookie is present on the request.
     *
     * @return bool
     */
    public function hasToken(): bool
    {
        return isset($_COOKIE[self::COOKIE_NAME]) && $_COOKIE[self::COOKIE_NAME] !== '';
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Delete the remember-me cookie from the client.
     *
     * @return void
     */
    private function clearCookie(): void
    {
        $isSecure = $this->isSecureRequest();

        setcookie(
            self::COOKIE_NAME,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );
    }

    private function isSecureRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        if (isset($_SERVER['REQUEST_SCHEME']) && strtolower((string)$_SERVER['REQUEST_SCHEME']) === 'https') {
            return true;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
            return true;
        }

        return isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443';
    }
}
