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

namespace Catalyst\Framework\Database;

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Exceptions\ConnectionException;
use Catalyst\Helpers\Log\Logger;

/**
 * Database connection manager
 *
 * Manages one or more named PDO connections. Supports multiple simultaneous
 * connections (db1, db2, …) as defined in boot-core/config/{env}/db.json.
 *
 * ## Configuration source (via ConfigManager)
 *
 * Primary (post-Setup Wizard):
 *   boot-core/config/{environment}/db.json
 *   {
 *     "db1": { "db_host":…, "db_port":…, "db_name":…, "db_user":…, "db_password":… },
 *     "db2": { … }
 *   }
 *
 * Fallback (first boot — JSON absent):
 *   DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD constants
 *   defined from the .env file in env-constant.php.
 *
 * ## Password encryption
 *
 * Passwords prefixed with "enc:" are encrypted and require the Crypt class,
 * implemented in Etapa 7 (Config Panel). For Etapa 1 development, plaintext
 * passwords are expected. Detecting "enc:" logs a warning and uses the raw
 * value — the connection will fail until Crypt is available.
 *
 * ## Usage
 *
 *   // Default connection
 *   DatabaseManager::getInstance()->connection()->table('users')->get();
 *
 *   // Named connection
 *   DatabaseManager::getInstance()->connection('db2')->table('orders')->get();
 *
 *   // Shorthand
 *   DatabaseManager::getInstance()->table('users')->get();
 *   DatabaseManager::getInstance()->table('orders', 'db2')->get();
 *
 * @package Catalyst\Framework\Database
 */
class DatabaseManager
{
    use SingletonTrait;

    /** @var array<string, Connection> Lazy-created PDO connections keyed by config name */
    private array $connections = [];

    /** @var array<string, array> Raw connection configs (db1 => [...], db2 => [...]) */
    private array $configs = [];

    private Logger $logger;

    private ?string $defaultConnection = null;

    /**
     * Initializes the Database Manager instance.
     */
    protected function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->loadConfigurations();
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Get a connection by name, or the default connection when $name is null.
     *
     * Connections are lazy-created: the PDO handshake happens on first use.
     *
     * @throws ConnectionException when the name is not configured
     */
    public function connection(?string $name = null): Connection
    {
        $key = $name ?? $this->defaultConnection;

        if (empty($key)) {
            throw new ConnectionException('No default database connection configured');
        }

        if (!isset($this->configs[$key])) {
            throw new ConnectionException("Database configuration '{$key}' not found");
        }

        if (!isset($this->connections[$key])) {
            $this->connections[$key] = $this->buildConnection($key, $this->configs[$key]);
        }

        return $this->connections[$key];
    }

    /**
     * Get a QueryBuilder for $table on the specified (or default) connection.
     *
     * @throws ConnectionException
     */
    public function table(string $table, ?string $connection = null): QueryBuilder
    {
        return $this->connection($connection)->table($table);
    }

    /**
     * Updates the default connection value.
     */
    public function setDefaultConnection(string $name): self
    {
        $this->defaultConnection = $name;
        return $this;
    }

    /** @return string[] All configured connection names */
    public function getConnectionNames(): array
    {
        return array_keys($this->configs);
    }

    /**
     * Determines whether has Connection.
     */
    public function hasConnection(string $name): bool
    {
        return isset($this->configs[$name]);
    }

    // -------------------------------------------------------------------------
    // Configuration loading
    // -------------------------------------------------------------------------

    /**
     * Load connection configs from ConfigManager (JSON) or fall back to .env constants.
     */
    private function loadConfigurations(): void
    {
        $dbSection = ConfigManager::getInstance()->section('db');

        if (!empty($dbSection)) {
            $this->configs           = $dbSection;
            $this->defaultConnection = array_key_first($this->configs);
            $this->logger->debug('DatabaseManager: config loaded from JSON', [
                'connections' => array_keys($this->configs),
            ]);
            return;
        }

        $this->loadFromEnvFallback();
    }

    /**
     * Build a single "default" connection from .env-derived DB_* constants.
     * Used only when no JSON config exists (first boot / Setup Wizard not yet run).
     */
    private function loadFromEnvFallback(): void
    {
        if (!defined('DB_DATABASE') || DB_DATABASE === '') {
            $this->logger->debug('DatabaseManager: no DB config available (DB_DATABASE is empty)');
            return;
        }

        $this->configs = [
            'default' => [
                'db_host'     => defined('DB_HOST')     ? DB_HOST     : 'localhost',
                'db_port'     => defined('DB_PORT')     ? DB_PORT     : 3306,
                'db_database' => defined('DB_DATABASE') ? DB_DATABASE : '',
                'db_username' => defined('DB_USERNAME') ? DB_USERNAME : '',
                'db_password' => defined('DB_PASSWORD') ? DB_PASSWORD : '',
            ],
        ];
        $this->defaultConnection = 'default';

        $this->logger->debug('DatabaseManager: config loaded from .env constants (first-boot fallback)');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate a Connection from a raw config array.
     *
     * Accepts both the canonical db.json keys (`db_database`, `db_username`) and
     * the legacy short aliases (`db_name`, `db_user`) for backward compatibility
     * with any config that still uses the older shape.
     */
    private function buildConnection(string $name, array $config): Connection
    {
        $database = (string)($config['db_database'] ?? $config['db_name'] ?? '');
        $username = (string)($config['db_username'] ?? $config['db_user'] ?? '');

        return new Connection(
            (string)($config['db_host'] ?? 'localhost'),
            (int)($config['db_port']    ?? 3306),
            $database,
            $username,
            $this->resolvePassword((string)($config['db_password'] ?? ''), $name),
            $name
        );
    }

    /**
     * Resolve the actual password string from a config value.
     *
     * Passwords stored with "enc:" are encrypted (requires Crypt, Etapa 7).
     * Until then, an "enc:" password logs a warning and passes through as-is —
     * the PDO connect will fail, which is the intended signal that Crypt is needed.
     */
    private function resolvePassword(string $password, string $connectionName): string
    {
        if (str_starts_with($password, 'enc:')) {
            $this->logger->warning(
                "Connection '{$connectionName}': encrypted password ('enc:' prefix) requires Crypt " .
                '(Etapa 7 — Config Panel). Using raw value until then.'
            );
        }

        return $password;
    }
}
