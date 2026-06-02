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

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\ConfigManager;
use Closure;
use Exception;

/**************************************************************************************
 * BasicAuthMiddleware class for HTTP Basic Authentication
 *
 * Provides HTTP Basic Authentication with file-based failed-attempt throttling.
 *
 * Audit note:
 * - implementation is real
 * - no active route consumers were confirmed in the current repo audit
 * - keep as an internal/legacy guard until a concrete runtime caller is restored
 *
 * @package Catalyst\Framework\Middleware
 */
/**
 * Defines the Basic Auth Middleware class contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the basic auth middleware behavior within its module boundary.
 */
class BasicAuthMiddleware extends CoreMiddleware
{
    // Configuration
    private const int MAX_ATTEMPTS = 3;
    private const int ATTEMPT_DELAY = 30; // seconds
    private const int LOCKOUT_TIME = 1800; // 30 minutes in seconds
    private const string STORAGE_PATH = 'logs/auth/attempts.json';

    /**
     * @throws Exception
     */
    public function process(Request $request, Closure $next): Response
    {
        // Get client IP address
        $clientIp = $request->getClientIp();

        // Check for basic auth credentials
        $username = $_SERVER['PHP_AUTH_USER'] ?? null;
        $password = $_SERVER['PHP_AUTH_PW'] ?? null;

        // Get expected credentials from environment or config
        $expectedUsername = $this->getConfigUsername();
        $expectedPassword = $this->getConfigPassword();

        // Check if client is allowed to attempt authentication
        // Only check if credentials are provided but incorrect
        if ($username !== null && $password !== null) {
            $authStatus = $this->checkAuthAttemptStatus($clientIp);
            if (!$authStatus['allowed']) {
                $response = new Response('Too many failed attempts. Please try again later.', 429);
                $response->setHeader('Retry-After', (string)$authStatus['wait_time']);
                return $response;
            }
        }

        // Verify credentials
        if (!$username || !$password || $username !== $expectedUsername || $password !== $expectedPassword) {
            // Only record failed attempt if credentials were provided
            if ($username !== null && $password !== null) {
                $this->recordFailedAttempt($clientIp);
            }

            $response = new Response('Authentication required', 401);
            $response->setHeader('WWW-Authenticate', 'Basic realm="Configuration Access ' . time() . '"');
            return $response;
        }

        // Authentication successful - reset failed attempts
        $this->resetFailedAttempts($clientIp);

        return $this->passToNext($request, $next);
    }

    /**
     * Check if client is allowed to attempt authentication
     *
     * @param string $clientIp Client IP address
     * @return array Status array with 'allowed' and 'wait_time' keys
     */
    protected function checkAuthAttemptStatus(string $clientIp): array
    {
        $attempts = $this->getFailedAttempts();

        // If no attempts for this IP, allow
        if (!isset($attempts[$clientIp])) {
            return ['allowed' => true, 'wait_time' => 0];
        }

        $clientAttempts = $attempts[$clientIp];
        $currentTime = time();
        $lastAttemptTime = $clientAttempts['last_attempt'];
        $attemptCount = $clientAttempts['count'];

        // Debug information - log to a file for troubleshooting
        $debugInfo = [
            'client_ip' => $clientIp,
            'current_time' => $currentTime,
            'last_attempt_time' => $lastAttemptTime,
            'attempt_count' => $attemptCount,
            'time_diff' => $lastAttemptTime - $currentTime
        ];
        file_put_contents(PD . '/logs/auth/debug.log', date('Y-m-d H:i:s') . ' - ' . json_encode($debugInfo) . PHP_EOL, FILE_APPEND);

        // Check for timestamp in the future (time discrepancy between servers)
        // If the timestamp is more than 5 minutes in the future, consider it invalid
        if ($lastAttemptTime > ($currentTime + 300)) {
            // Reset the attempts for this IP completely
            $this->resetFailedAttempts($clientIp);
            return ['allowed' => true, 'wait_time' => 0];
        }
        // If timestamp is slightly in the future, adjust it to current time
        else if ($lastAttemptTime > $currentTime) {
            $lastAttemptTime = $currentTime;

            // Update the stored value
            $attempts[$clientIp]['last_attempt'] = $currentTime;
            $this->saveFailedAttempts($attempts);
        }

        // Client is in lockout period after 3 failed attempts
        if ($attemptCount >= self::MAX_ATTEMPTS) {
            $lockoutEndsAt = $lastAttemptTime + self::LOCKOUT_TIME;

            if ($currentTime < $lockoutEndsAt) {
                $waitTime = $lockoutEndsAt - $currentTime;
                return ['allowed' => false, 'wait_time' => $waitTime];
            }

            // Lockout period is over, reset attempts
            $this->resetFailedAttempts($clientIp);
            return ['allowed' => true, 'wait_time' => 0];
        }

        // Check if we need to enforce delay between attempts
        if ($attemptCount > 0) {
            $nextAllowedAttemptTime = $lastAttemptTime + self::ATTEMPT_DELAY;

            if ($currentTime < $nextAllowedAttemptTime) {
                $waitTime = $nextAllowedAttemptTime - $currentTime;
                return ['allowed' => false, 'wait_time' => $waitTime];
            }
        }

        return ['allowed' => true, 'wait_time' => 0];
    }

    /**
     * Record a failed authentication attempt
     *
     * @param string $clientIp Client IP address
     * @return void
     */
    protected function recordFailedAttempt(string $clientIp): void
    {
        $attempts = $this->getFailedAttempts();

        if (!isset($attempts[$clientIp])) {
            $attempts[$clientIp] = [
                'count' => 0,
                'last_attempt' => 0
            ];
        }

        $attempts[$clientIp]['count']++;
        $attempts[$clientIp]['last_attempt'] = time();

        $this->saveFailedAttempts($attempts);
    }

    /**
     * Reset failed attempts counter for an IP
     *
     * @param string $clientIp Client IP address
     * @return void
     */
    protected function resetFailedAttempts(string $clientIp): void
    {
        $attempts = $this->getFailedAttempts();

        if (isset($attempts[$clientIp])) {
            unset($attempts[$clientIp]);
            $this->saveFailedAttempts($attempts);
        }
    }

    /**
     * Get all failed authentication attempts
     *
     * @return array Failed attempts data
     */
    protected function getFailedAttempts(): array
    {
        $storagePath = $this->getStoragePath();

        if (!file_exists($storagePath)) {
            return [];
        }

        $content = file_get_contents($storagePath);
        if (!$content) {
            return [];
        }

        $attempts = json_decode($content, true);
        return is_array($attempts) ? $attempts : [];
    }

    /**
     * Save failed attempts data
     *
     * @param array $attempts Failed attempts data
     * @return void
     */
    protected function saveFailedAttempts(array $attempts): void
    {
        $storagePath = $this->getStoragePath();

        // Ensure directory exists
        $dir = dirname($storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($storagePath, json_encode($attempts));
    }

    /**
     * Get storage path for failed attempts
     *
     * @return string Full path to storage file
     */
    protected function getStoragePath(): string
    {
        return implode(DS, [PD, self::STORAGE_PATH]);
    }

    /**
     * Get configured username for authentication
     *
     * @return string Expected username
     */
    protected function getConfigUsername(): string
    {
        // First, try to get from defined constant
        if (defined('CONFIG_USERNAME')) {
            return CONFIG_USERNAME;
        }

        // Then, try from configuration manager
        $configUsername = $this->getConfigValue('tools.config.username');
        if ($configUsername !== null) {
            return $configUsername;
        }

        // Default fallback
        return 'admin';
    }

    /**
     * Get configured password for authentication
     *
     * @return string Expected password
     */
    protected function getConfigPassword(): string
    {
        // First, try to get from defined constant
        if (defined('CONFIG_PASSWORD')) {
            return CONFIG_PASSWORD;
        }

        // Then, try from configuration manager
        $configPassword = $this->getConfigValue('tools.config.password');
        if ($configPassword !== null) {
            return $configPassword;
        }

        // Default fallback
        return 'admin';
    }

    /**
     * Returns the config value value.
     */
    protected function getConfigValue(string $key): ?string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();
            $value = $configManager->get($key);
        } catch (Exception) {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $stringValue = trim((string)$value);
        return $stringValue !== '' ? $stringValue : null;
    }
}
