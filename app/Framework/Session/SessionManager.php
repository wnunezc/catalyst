<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework
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
 * SessionManager component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Session;

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Log\Logger;
use Exception;
use RuntimeException;

/**
 * SessionManager class for managing application sessions
 *
 * Handles session initialization, data storage/retrieval, and flash messages.
 *
 * @package Catalyst\Framework\Session
 */
class SessionManager
{
    use SingletonTrait;

    private const OLD_INPUT_KEY = '_old_input';
    private const VALIDATION_ERRORS_KEY = '_validation_errors';

    /**
     * Whether the session has been initialized
     *
     * @var bool
     */
    protected bool $initialized = false;

    /**
     * Session configuration — environment-aware defaults (G11)
     *
     * @var array
     */
    protected array $config = [
        'driver'               => 'file',
        'connection'           => 'db1',
        'table'                => 'sessions',
        'name'                 => 'catalyst-session',
        'lifetime'             => 2592000,        // 30 days
        'activity_timeout'     => 172800,          // 2 days
        'use_activity_timeout' => true,
        'secure'               => false,           // overridden in init() based on environment
        'httponly'             => true,
        'samesite'             => 'Strict',
        'domain'               => '',              // empty = current domain
    ];

    /**
     * Get the session configuration
     *
     * @return array Session configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set the session configuration
     *
     * @param array $config Configuration options
     * @return self For method chaining
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Initialize the session with provided configuration
     *
     * @param array $config Configuration options
     * @return self For method chaining
     * @throws RuntimeException|Exception If session cannot be started
     */
    public function init(array $config = []): self
    {
        if ($this->initialized) {
            return $this;
        }

        if (empty($config)) {
            try {
                $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();

                if ($configManager instanceof ConfigManager) {
                    $sessionConfig = $configManager->entry('session', 'session');
                    $config = [
                        'driver'               => (string)($sessionConfig['session_driver'] ?? $this->config['driver']),
                        'connection'           => (string)($sessionConfig['session_connection'] ?? $this->config['connection']),
                        'table'                => (string)($sessionConfig['session_table'] ?? $this->config['table']),
                        'name'                 => (string)($sessionConfig['session_name'] ?? $this->config['name']),
                        'lifetime'             => (int)($sessionConfig['session_lifetime'] ?? $this->config['lifetime']),
                        'activity_timeout'     => (int)($sessionConfig['session_activity_timeout'] ?? $this->config['activity_timeout']),
                        'use_activity_timeout' => (bool)($sessionConfig['session_use_activity_timeout'] ?? $this->config['use_activity_timeout']),
                        'secure'               => array_key_exists('session_secure', $sessionConfig)
                            ? (bool)$sessionConfig['session_secure']
                            : $this->resolveSecureDefault(),
                        'httponly'             => array_key_exists('session_http_only', $sessionConfig)
                            ? (bool)$sessionConfig['session_http_only']
                            : $this->config['httponly'],
                        'samesite'             => (string)($sessionConfig['session_same_site'] ?? $this->config['samesite']),
                        'domain'               => (string)($sessionConfig['session_domain'] ?? $this->config['domain']),
                    ];
                }
            } catch (\Throwable) {
                $config = [];
            }
        }

        // Tier 2: environment-aware fallback if no config available (G11)
        if (empty($config)) {
            $config['secure'] = $this->resolveSecureDefault();
        }

        if (!empty($config)) {
            $this->setConfig($config);
        }

        // Warn if production runs without Secure flag (G-SM1 fix: check here after config merge)
        if (!$this->config['secure'] && defined('IS_PRODUCTION') && IS_PRODUCTION) {
            Logger::getInstance()->warning(
                'Session cookie running without Secure flag in production. ' .
                'Set SESSION_SECURE=true in .env or ensure SSL is handled upstream.'
            );
        }

        // Hardening: strict mode prevents accepting external session IDs (session fixation) (G-SM3)
        ini_set('session.use_strict_mode', '1');

        // Hardening: align PHP GC lifetime with cookie lifetime so sessions expire consistently (G-SM2)
        ini_set('session.gc_maxlifetime', (string)$this->config['lifetime']);

        $this->configureSessionHandler();

        // Configure session cookie parameters
        session_set_cookie_params([
            'lifetime' => $this->config['lifetime'],
            'path'     => '/',
            'domain'   => $this->config['domain'],
            'secure'   => $this->config['secure'],
            'httponly' => $this->config['httponly'],
            'samesite' => $this->config['samesite'],
        ]);

        // Set session name
        session_name($this->config['name']);

        // Start the session
        if (session_status() === PHP_SESSION_NONE) {
            if (!session_start()) {
                throw new RuntimeException('Failed to start session');
            }
        }

        // Check activity timeout if enabled (G-SM5: verify session_start() after destroy)
        if ($this->config['use_activity_timeout'] && isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > $this->config['activity_timeout']) {
                $this->destroy();

                if (!session_start()) {
                    throw new RuntimeException('Failed to restart session after activity timeout');
                }
            }
        }

        // Update last activity time
        $_SESSION['_last_activity'] = time();

        $this->initialized = true;

        // Log session initialization if in development mode
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT && class_exists('\Catalyst\Helpers\Log\Logger')) {
            Logger::getInstance()->debug('Session initialized', [
                'name' => $this->config['name'],
                'lifetime' => $this->config['lifetime'],
                'use_activity_timeout' => $this->config['use_activity_timeout']
            ]);
        }

        return $this;
    }

    /**
     * Check if a session variable exists
     *
     * @param string $key Session variable key
     * @return bool True if the variable exists
     */
    public function has(string $key): bool
    {
        $this->ensureInitialized();
        return isset($_SESSION[$key]);
    }

    /**
     * Get a session variable
     *
     * @param string $key Session variable key
     * @param mixed $default Default value if not found
     * @return mixed Session variable value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureInitialized();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session variable
     *
     * @param string $key Session variable key
     * @param mixed $value Session variable value
     * @return self For method chaining
     */
    public function set(string $key, mixed $value): self
    {
        $this->ensureInitialized();
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * Remove a session variable
     *
     * @param string $key Session variable key
     * @return self For method chaining
     */
    public function remove(string $key): self
    {
        $this->ensureInitialized();
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * Get all session data
     *
     * @return array All session data
     */
    public function all(): array
    {
        $this->ensureInitialized();
        return $_SESSION;
    }

    /**
     * Clear all session data
     *
     * @return self For method chaining
     */
    public function clear(): self
    {
        $this->ensureInitialized();
        $_SESSION = [];
        return $this;
    }

    /**
     * Destroy the current session
     *
     * @return self For method chaining
     */
    public function destroy(): self
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Clear session data
            $_SESSION = [];

            // Delete the session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    [
                        'expires' => time() - 42000,
                        'path' => $params['path'],
                        'domain' => $params['domain'],
                        'secure' => $params['secure'],
                        'httponly' => $params['httponly'],
                        'samesite' => $params['samesite'] ?? $this->config['samesite']
                    ]
                );
            }

            // Destroy the session
            session_destroy();
        }

        $this->initialized = false;
        return $this;
    }

    /**
     * Regenerate the session ID
     *
     * @param bool $deleteOldSession Whether to delete the old session data
     * @return self For method chaining
     */
    public function regenerateId(bool $deleteOldSession = true): self
    {
        $this->ensureInitialized();
        session_regenerate_id($deleteOldSession);
        return $this;
    }

    /**
     * Check if the session is initialized
     *
     * @return bool True if the session is initialized
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * @param array<string, mixed> $targetConfig
     */
    public function seedActiveSession(array $targetConfig): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $sessionId = session_id();
        if ($sessionId === '') {
            return false;
        }

        $payload = session_encode();
        if ($payload === false) {
            Logger::getInstance()->warning('Unable to encode active session payload for session backend migration.');
            return false;
        }

        $driver = strtolower((string)($targetConfig['driver'] ?? 'file'));

        if ($driver === 'database') {
            $connection = (string)($targetConfig['connection'] ?? 'db1');
            $table = $this->sanitizeSessionTable((string)($targetConfig['table'] ?? 'sessions'));

            $handler = new DatabaseSessionHandler($connection, $table);
            return $handler->open('', session_name()) && $handler->write($sessionId, $payload);
        }

        if ($driver === 'file') {
            return $this->writeNativeSessionFile($sessionId, $payload);
        }

        Logger::getInstance()->warning('Unable to migrate active session to unsupported driver.', [
            'driver' => $driver,
        ]);

        return false;
    }

    /**
     * @param array<string, mixed> $input
     */
    public function flashOldInput(array $input): self
    {
        $this->ensureInitialized();
        $_SESSION[self::OLD_INPUT_KEY] = $this->sanitizeOldInput($input);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function peekOldInput(): array
    {
        $this->ensureInitialized();

        $input = $_SESSION[self::OLD_INPUT_KEY] ?? [];

        return is_array($input) ? $input : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function consumeOldInput(): array
    {
        $input = $this->peekOldInput();
        unset($_SESSION[self::OLD_INPUT_KEY]);

        return $input;
    }

    /**
     * @param array<string, string[]|string> $errors
     */
    public function flashValidationErrors(array $errors, string $bag = 'default'): self
    {
        $this->ensureInitialized();

        $bags = $_SESSION[self::VALIDATION_ERRORS_KEY] ?? [];
        if (!is_array($bags)) {
            $bags = [];
        }

        $bags[$bag] = $this->normalizeValidationErrors($errors);
        $_SESSION[self::VALIDATION_ERRORS_KEY] = $bags;

        return $this;
    }

    /**
     * @return array<string, array<string, string[]>>
     */
    public function peekValidationErrors(): array
    {
        $this->ensureInitialized();

        $bags = $_SESSION[self::VALIDATION_ERRORS_KEY] ?? [];

        return is_array($bags) ? $bags : [];
    }

    /**
     * @return array<string, array<string, string[]>>
     */
    public function consumeValidationErrors(): array
    {
        $errors = $this->peekValidationErrors();
        unset($_SESSION[self::VALIDATION_ERRORS_KEY]);

        return $errors;
    }

    public function clearFormState(): self
    {
        $this->ensureInitialized();

        unset($_SESSION[self::OLD_INPUT_KEY], $_SESSION[self::VALIDATION_ERRORS_KEY]);

        return $this;
    }

    /**
     * Resolve the secure cookie default based on environment (G11).
     * Development: false (HTTP allowed). Production/Staging: true (HTTPS required).
     * Production warning is logged in init() after full config merge.
     *
     * @return bool
     */
    protected function resolveSecureDefault(): bool
    {
        return defined('IS_PRODUCTION') && IS_PRODUCTION;
    }

    private function configureSessionHandler(): void
    {
        $driver = strtolower((string)($this->config['driver'] ?? 'file'));

        if ($driver === 'database') {
            $connection = (string)($this->config['connection'] ?? 'db1');
            $table = $this->sanitizeSessionTable((string)($this->config['table'] ?? 'sessions'));

            session_set_save_handler(new DatabaseSessionHandler($connection, $table), true);
            return;
        }

        if ($driver !== 'file') {
            Logger::getInstance()->warning('Unknown session driver requested; falling back to native file sessions.', [
                'driver' => $driver,
            ]);
        }
    }

    /**
     * Ensure the session is initialized
     *
     * @return void
     * @throws RuntimeException If session is not initialized
     */
    protected function ensureInitialized(): void
    {
        if (!$this->initialized) {
            throw new RuntimeException('Session not initialized. Call init() first.');
        }
    }

    private function sanitizeSessionTable(string $table): string
    {
        $table = trim($table);

        if ($table === '' || preg_match('/^[A-Za-z0-9_]+$/', $table) !== 1) {
            return 'sessions';
        }

        return $table;
    }

    private function writeNativeSessionFile(string $sessionId, string $payload): bool
    {
        $savePath = (string)session_save_path();
        if ($savePath === '') {
            $savePath = sys_get_temp_dir();
        }

        if (str_contains($savePath, ';')) {
            $segments = explode(';', $savePath);
            $savePath = (string)end($segments);
        }

        $savePath = trim($savePath);
        if ($savePath === '') {
            $savePath = sys_get_temp_dir();
        }

        if (!is_dir($savePath) || !is_writable($savePath)) {
            Logger::getInstance()->warning('Unable to migrate active session to native file backend.', [
                'path' => $savePath,
            ]);

            return false;
        }

        $targetFile = rtrim($savePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
        $bytes = @file_put_contents($targetFile, $payload, LOCK_EX);

        if ($bytes === false) {
            Logger::getInstance()->warning('Failed to write migrated native session file.', [
                'path' => $targetFile,
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function sanitizeOldInput(array $input): array
    {
        $sanitized = [];

        foreach ($input as $key => $value) {
            $sanitized[$key] = $this->sanitizeOldInputValue($value);
        }

        return $sanitized;
    }

    private function sanitizeOldInputValue(mixed $value): mixed
    {
        if ($value instanceof \Catalyst\Framework\Http\UploadedFile || is_resource($value)) {
            return null;
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->sanitizeOldInputValue($item), $value);
        }

        if (is_scalar($value) || $value === null) {
            return $value;
        }

        return null;
    }

    /**
     * @param array<string, string[]|string> $errors
     * @return array<string, string[]>
     */
    private function normalizeValidationErrors(array $errors): array
    {
        $normalized = [];

        foreach ($errors as $field => $messages) {
            if (is_array($messages)) {
                $normalized[$field] = array_values(array_map('strval', $messages));
                continue;
            }

            $normalized[$field] = [(string) $messages];
        }

        return $normalized;
    }
}
