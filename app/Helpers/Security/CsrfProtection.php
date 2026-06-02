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

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;
use Random\RandomException;

/**************************************************************************************
 * CsrfProtection component for the Catalyst Framework.
 *
 * This class provides methods to generate, validate, and manage CSRF tokens to protect
 * against Cross-Site Request Forgery attacks. It uses a session-based approach to
 * store tokens and includes functionality to limit the number of stored tokens,
 * set token expiration times, and clean up expired tokens.
 *
 * The class also supports logging for debugging purposes.
 *
 * @package Catalyst\Helpers\Security
 */
/**
 * Defines the Csrf Protection class contract.
 *
 * @package Catalyst\Helpers\Security
 * Responsibility: Coordinates the csrf protection behavior within its module boundary.
 */
class CsrfProtection
{
    use SingletonTrait;

    /**
     * Session key for storing CSRF tokens
     */
    private const string SESSION_KEY = 'catalyst_csrf_tokens';

    /**
     * Default token expiration time (30 minutes)
     */
    private const int DEFAULT_EXPIRY = 1800; // 30 minutes

    /**
     * Maximum number of tokens to store
     */
    private const int MAX_TOKENS = 50;

    /**
     * Logger instance
     *
     * @var Logger|null
     */
    protected ?Logger $logger = null;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->logger = Logger::getInstance();
    }

    /**
     * Generate a new CSRF token
     *
     * @param string|null $action Optional action context for the token
     * @param int|null $expiry Token expiration time in seconds
     * @return string The generated token
     * @throws RandomException
     * @throws Exception
     */
    public function generateToken(?string $action = null, ?int $expiry = null): string
    {
        // Start a session if not already started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Initialize a token array if it doesn't exist
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        // Clean expired tokens
        $this->cleanExpiredTokens();

        // Generate a cryptographically secure random token
        $token = bin2hex(random_bytes(32));

        // Store token with metadata
        $_SESSION[self::SESSION_KEY][$token] = [
            'action' => $action,
            'expires' => time() + ($expiry ?? self::DEFAULT_EXPIRY)
        ];

        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT && $this->logger) {
            $this->logger->debug('CSRF token generated', [
                'action' => $action,
                'session_status' => session_status(),
                'token_store_size' => count($_SESSION[self::SESSION_KEY]),
                'expires_in_seconds' => $expiry ?? self::DEFAULT_EXPIRY,
            ]);
        }

        // Manage token limit with an improved strategy
        $this->manageTokenLimit();

        return $token;
    }

    /**
     * Validate a CSRF token
     *
     * @param string $token Token to validate
     * @param string|null $action Optional action context for validation
     * @param bool $removeOnSuccess Whether to remove the token after successful validation
     * @return bool Whether the token is valid
     * @throws Exception
     */
    public function validateToken(string $token, ?string $action = null, bool $removeOnSuccess = false): bool
    {
        // Start a session if not already started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT && $this->logger) {
            $this->logger->debug('CSRF validation track', [
                'token_present' => $token !== '',
                'action' => $action,
                'remove_on_success' => $removeOnSuccess,
                'token_store_size' => isset($_SESSION[self::SESSION_KEY]) && is_array($_SESSION[self::SESSION_KEY])
                    ? count($_SESSION[self::SESSION_KEY])
                    : 0,
            ]);
        }

        // If no tokens exist, validation fails
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        // Check if token exists
        if (!isset($_SESSION[self::SESSION_KEY][$token])) {
            return false;
        }

        $tokenData = $_SESSION[self::SESSION_KEY][$token];

        // Check if the token has expired
        if ($tokenData['expires'] < time()) {
            // Remove expired token
            unset($_SESSION[self::SESSION_KEY][$token]);
            return false;
        }

        // Check action if specified
        if ($action !== null && $tokenData['action'] !== $action) {
            return false;
        }

        // Token is valid, remove if required
        if ($removeOnSuccess) {
            unset($_SESSION[self::SESSION_KEY][$token]);
        }

        return true;
    }

    /**
     * Clean expired tokens from the session
     *
     * @return void
     */
    private function cleanExpiredTokens(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            return;
        }

        $now = time();
        foreach ($_SESSION[self::SESSION_KEY] as $token => $data) {
            if ($data['expires'] < $now) {
                unset($_SESSION[self::SESSION_KEY][$token]);
            }
        }
    }

    /**
     * Manage the number of tokens to stay within the limit
     *
     * @return void
     */
    private function manageTokenLimit(): void
    {
        if (count($_SESSION[self::SESSION_KEY]) <= self::MAX_TOKENS) {
            return;
        }

        // If we're over the limit, use a more sophisticated approach
        // First, sort tokens by expiration time (oldest first)
        $tokens = $_SESSION[self::SESSION_KEY];
        uasort($tokens, function ($a, $b) {
            return $a['expires'] <=> $b['expires'];
        });

        // Remove oldest tokens until under limit
        $tokensToRemove = count($tokens) - self::MAX_TOKENS;
        $i = 0;
        foreach ($tokens as $token => $data) {
            if ($i >= $tokensToRemove) break;
            unset($_SESSION[self::SESSION_KEY][$token]);
            $i++;
        }
    }

    /**
     * Get HTML input field for CSRF token
     *
     * @param string|null $action Optional action context for the token
     * @return string HTML input field
     * @throws RandomException
     */
    public function getTokenField(?string $action = null): string
    {
        $token = $this->generateToken($action);
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Get a CSRF token for use in JavaScript
     *
     * @param string|null $action Optional action context for the token
     * @return string The generated token
     * @throws RandomException
     * @throws Exception
     */
    public function getJsToken(?string $action = null): string
    {
        return $this->generateToken($action);
    }

    /**
     * Get HTML meta-tag for CSRF token
     * Useful for SPA applications that need to access the token via JavaScript
     *
     * @param string|null $action Optional action context for the token
     * @return string HTML meta tag
     * @throws RandomException
     * @throws Exception
     */
    public function getMetaTag(?string $action = null): string
    {
        $token = $this->generateToken($action);
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}
