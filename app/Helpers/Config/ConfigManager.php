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

namespace Catalyst\Helpers\Config;

use Catalyst\Framework\Cache\BootstrapCacheManager;
use Catalyst\Framework\Cache\CacheSettings;
use Catalyst\Framework\Traits\SingletonTrait;
use Exception;

/**
 * Framework configuration manager
 *
 * Loads all JSON configuration files for the active environment and exposes
 * them through dot-notation access. This is the authoritative source for
 * JSON-backed runtime configuration after the early bootstrap has completed.
 * Bootstrap-only concerns that must exist before autoload/config classes are
 * available (for example APP_ENV resolution in env-constant.php) still start
 * from .env and may be mirrored here later in the request lifecycle.
 *
 * ## Directory layout
 *
 *   boot-core/config/{environment}/
 *   +-- db.json        → { "db1": {...}, "db2": {...} }
 *   +-- mail.json      → { "mail1": {...}, "mail2": {...} }
 *   +-- app.json       → { "company": {...}, "project": {...} }
 *   +-- ...
 *
 * Each JSON file becomes a named section. Multiple named instances inside a
 * section (db1/db2, mail1/mail2, etc.) are preserved as-is.
 *
 * ## Fallback on first boot
 *
 * If the config directory is absent or empty (Setup Wizard not yet run),
 * ConfigManager initialises with an empty config array and reports
 * isConfigured() = false. Each Manager (DatabaseManager, MailManager…)
 * is responsible for falling back to .env constants in that scenario.
 *
 * ## Usage
 *
 *   // Dot-notation — any depth
 *   ConfigManager::getInstance()->get('db.db1.db_host');       // 'localhost'
 *   ConfigManager::getInstance()->get('mail.mail1.mail_port'); // 587
 *
 *   // Full section — iterate all named instances
 *   $dbConfigs = ConfigManager::getInstance()->section('db');
 *   // ['db1' => [...], 'db2' => [...]]
 *
 *   // Existence check
 *   ConfigManager::getInstance()->has('db.db1.db_host');
 *
 *   // Post-wizard check
 *   ConfigManager::getInstance()->isConfigured();
 *
 * @package Catalyst\Helpers\Config
 */
class ConfigManager
{
    use SingletonTrait;

    /** @var array<string, array> All loaded config sections, keyed by filename (without .json) */
    private array $config = [];

    private string $environment;

    private bool $configured = false;

    private ConfigSecretStore $secretStore;

    /**
     * @throws Exception
     */
    protected function __construct()
    {
        $this->environment = $this->resolveEnvironment();
        $this->secretStore = new ConfigSecretStore($this->environment);
        $this->load();
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Retrieve a config value by dot-notation key.
     *
     * Examples:
     *   get('db.db1.db_host')       → 'localhost'
     *   get('mail.mail1.mail_port') → 587
     *   get('app.project.project_name', 'Catalyst') → value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $data     = $this->config;

        foreach ($segments as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * Check whether a dot-notation key exists and is non-null.
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Return all instances within a section (e.g. 'db' → ['db1'=>[...], 'db2'=>[...]]).
     * Returns null when the section does not exist.
     *
     * @return array<string, array>|null
     */
    public function section(string $section): ?array
    {
        return isset($this->config[$section]) && is_array($this->config[$section])
            ? $this->config[$section]
            : null;
    }

    /**
     * Return the full config array (all sections).
     *
     * @return array<string, array>
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Return .env-derived defaults for a single section.
     *
     * @return array<string, mixed>
     */
    public function defaults(string $section): array
    {
        return $this->readDefaults()[strtolower($section)] ?? [];
    }

    /**
     * Return one named entry inside a section, merged over .env-derived defaults.
     *
     * Typical mappings:
     *   app        → project
     *   session    → session
     *   cache      → cache
     *   logging    → logging
     *   security   → security
     *   websocket  → websocket
     *   cors       → cors
     *   db         → db1
     *   mail       → mail1
     *
     * @param array<string, mixed>|null $defaults
     * @return array<string, mixed>
     */
    public function entry(string $section, string $entry, ?array $defaults = null): array
    {
        $resolvedDefaults = $defaults ?? $this->defaults($section);
        $rawSection       = $this->section($section);
        $rawEntry         = $rawSection[$entry] ?? null;

        if (!is_array($rawEntry)) {
            return $resolvedDefaults;
        }

        return array_replace($resolvedDefaults, $rawEntry);
    }

    /**
     * True when the Setup Wizard has run and app.json is present with
     * project_config = true. False on first boot.
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * Return the resolved environment name (development | staging | testing | production).
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    // -------------------------------------------------------------------------
    // Write API
    // -------------------------------------------------------------------------

    /**
     * Persist a config section to disk and update the in-memory cache.
     *
     * Writes (or overwrites) boot-core/config/{environment}/{section}.json.
     * Creates the config directory if it does not exist.
     * Re-evaluates isConfigured() after the write.
     *
     * @param string               $section  Section name (e.g. 'app', 'db', 'mail')
     * @param array<string, mixed> $data     Full section data to write
     * @throws \RuntimeException on directory creation or write failure
     */
    public function writeSection(string $section, array $data): void
    {
        $configDir = implode(DS, [PD, 'boot-core', 'config', $this->environment]);

        if (!is_dir($configDir) && !mkdir($configDir, 0750, true)) {
            throw new \RuntimeException("ConfigManager: cannot create directory '{$configDir}'");
        }

        $section = strtolower($section);
        $file    = $configDir . DS . strtolower($section) . '.json';
        $split = ConfigSecretCatalog::splitSection($section, $data);
        $publicData = $split['public'];
        $secretData = $split['secrets'];
        $encoded = json_encode($publicData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($encoded === false || file_put_contents($file, $encoded) === false) {
            throw new \RuntimeException("ConfigManager: cannot write section '{$section}' to '{$file}'");
        }

        if (ConfigSecretCatalog::managesSection($section)) {
            $this->secretStore->persistSection($section, $secretData);
        }

        $this->config[$section] = ConfigSecretCatalog::mergeSection($section, $publicData, $secretData);
        $this->configured = $this->detectConfigured();
        BootstrapCacheManager::syncConfigCache($this->config);
    }

    /**
     * Return default values for all config sections from GET_ENV_VAR.
     * Used by ConfigController to pre-populate forms on first boot.
     *
     * @return array<string, mixed>
     */
    public function readDefaults(): array
    {
        $env = defined('GET_ENV_VAR') ? GET_ENV_VAR : [];

        return [
            'app' => [
                'project_name'     => $env['APP_NAME']      ?? '',
                'project_url'      => $env['APP_URL']       ?? '',
                'project_env'      => $env['APP_ENV']       ?? 'production',
                'project_lang'     => $env['APP_LANG']      ?? 'en',
                'project_timezone' => $env['APP_TIMEZONE']  ?? 'UTC',
                'project_entry'    => $env['APP_ENTRY']     ?? '',
                'project_entry_secondary' => $env['APP_ENTRY_SECONDARY'] ?? '',
                'project_key'      => $env['APP_KEY']       ?? '',
                'project_debug'    => ($env['APP_DEBUG']    ?? 'false') === 'true',
            ],
            'db' => [
                'db_host'     => $env['DB_HOST']     ?? 'localhost',
                'db_port'     => (int)($env['DB_PORT']     ?? 3306),
                'db_database' => $env['DB_DATABASE'] ?? '',
                'db_username' => $env['DB_USERNAME'] ?? '',
                'db_password' => $env['DB_PASSWORD'] ?? '',
            ],
            'mail' => [
                'mail_host'         => $env['MAIL_HOST']         ?? '',
                'mail_port'         => (int)($env['MAIL_PORT']         ?? 587),
                'mail_username'     => $env['MAIL_USERNAME']     ?? '',
                'mail_password'     => $env['MAIL_PASSWORD']     ?? '',
                'mail_encryption'   => $env['MAIL_ENCRYPTION']   ?? 'tls',
                'mail_from_address' => $env['MAIL_FROM_ADDRESS'] ?? '',
                'mail_from_name'    => $env['MAIL_FROM_NAME']    ?? '',
            ],
            'ftp' => [
                'ftp_protocol' => $env['FTP_PROTOCOL'] ?? ((($env['FTP_SSL'] ?? 'false') === 'true') ? 'ftps' : 'ftp'),
                'ftp_host'    => $env['FTP_HOST']    ?? '',
                'ftp_port'    => (int)($env['FTP_PORT']    ?? ((($env['FTP_PROTOCOL'] ?? '') === 'sftp') ? 22 : 21)),
                'ftp_username' => $env['FTP_USERNAME'] ?? '',
                'ftp_password' => $env['FTP_PASSWORD'] ?? '',
                'ftp_root'    => $env['FTP_ROOT']    ?? '/',
                'ftp_timeout' => (int)($env['FTP_TIMEOUT'] ?? 10),
                'ftp_ssl'     => ($env['FTP_SSL']     ?? 'false') === 'true',
                'ftp_passive' => ($env['FTP_PASSIVE'] ?? 'true')  === 'true',
            ],
            'session' => [
                'session_driver'    => 'file',
                'session_connection'=> 'db1',
                'session_table'     => 'sessions',
                'session_name'      => $env['SESSION_NAME']      ?? 'catalyst-session',
                'session_lifetime'  => (int)($env['SESSION_LIFE_TIME'] ?? 2592000),
                'session_activity_timeout' => (int)($env['SESSION_ACTIVITY_TIMEOUT'] ?? 172800),
                'session_use_activity_timeout' => !isset($env['SESSION_ACTIVITY_TIMEOUT_ENABLED'])
                    || ($env['SESSION_ACTIVITY_TIMEOUT_ENABLED'] === 'true'),
                'session_secure'    => ($env['SESSION_SECURE']    ?? 'true')  === 'true',
                'session_http_only' => ($env['SESSION_HTTP_ONLY'] ?? 'true')  === 'true',
                'session_same_site' => $env['SESSION_SAME_SITE'] ?? 'Strict',
                'session_domain'    => $env['SESSION_DOMAIN']    ?? '',
            ],
            'cache' => [
                'cache_enabled' => false,
                'cache_driver' => 'file',
                'cache_prefix' => 'catalyst_',
                'app_cache' => false,
                'config_cache' => false,
                'discovery_cache' => false,
                'route_cache' => false,
            ],
            'logging' => [
                'log_channel'             => $env['LOG_CHANNEL'] ?? 'single',
                'log_level'               => $env['LOG_LEVEL'] ?? 'debug',
                'display_logs'            => ($env['APP_DISPLAY_LOGS'] ?? 'true') === 'true',
                'log_rotation_enabled'    => ($env['LOG_ROTATION_ENABLED'] ?? 'true') === 'true',
                'log_max_file_size_mb'    => (int)($env['LOG_MAX_FILE_SIZE_MB'] ?? 10),
                'log_max_rotated_files'   => (int)($env['LOG_MAX_ROTATED_FILES'] ?? 7),
            ],
            'security' => [
                'bcrypt_rounds' => (int)($env['BCRYPT_ROUNDS'] ?? 12),
                'mfa_enabled'   => false,
            ],
            'websocket' => [
                'enabled'          => true,
                'ws_port'          => (int)($env['WS_PORT']          ?? 8080),
                'ws_host'          => $env['WS_HOST']          ?? '0.0.0.0',
                'ws_internal_port' => (int)($env['WS_INTERNAL_PORT'] ?? 8181),
                'ws_publisher_url' => $env['WS_PUBLISHER_URL'] ?? 'http://127.0.0.1:8181/publish',
            ],
            'queue' => [
                'enabled' => true,
                'connection' => 'db1',
                'default_queue' => 'default',
                'jobs_table' => 'queue_jobs',
                'failed_jobs_table' => 'failed_jobs',
                'stale_after_seconds' => 300,
            ],
            'schedule' => [
                'enabled' => true,
                'history_table' => 'scheduler_runs',
            ],
            'cors' => [
                'enabled'           => true,
                'allowed_origins'   => ['*'],
                'allowed_methods'   => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'allowed_headers'   => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN'],
                'exposed_headers'   => [],
                'allow_credentials' => false,
                'max_age'           => 86400,
            ],
            'devtools' => [
                'app_debug'       => ($env['APP_DEBUG']        ?? 'false') === 'true',
                'display_logs'    => ($env['APP_DISPLAY_LOGS'] ?? 'true')  === 'true',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Loading (private)
    // -------------------------------------------------------------------------

    /**
     * Load all JSON files from boot-core/config/{environment}/.
     *
     * Fails silently when the directory does not exist (first boot).
     * Throws on malformed JSON to surface configuration errors early.
     *
     * @throws Exception on invalid JSON
     */
    private function load(): void
    {
        $cachedConfig = BootstrapCacheManager::loadConfigCache();
        if (is_array($cachedConfig) && $cachedConfig !== []) {
            $this->config = $cachedConfig;
            $this->configured = $this->detectConfigured();
            return;
        }

        $configDir = implode(DS, [PD, 'boot-core', 'config', $this->environment]);

        if (!is_dir($configDir)) {
            return;
        }

        $files = glob($configDir . DS . '*.json') ?: [];

        foreach ($files as $file) {
            if (strtolower(pathinfo($file, PATHINFO_FILENAME)) === 'secrets') {
                continue;
            }

            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(
                    'Invalid JSON in config file "' . basename($file) . '": ' . json_last_error_msg()
                );
            }

            if (is_array($data)) {
                $section                = strtolower(pathinfo($file, PATHINFO_FILENAME));
                $this->config[$section] = $data;
            }
        }

        $this->config = $this->secretStore->mergeIntoConfig($this->config);
        $this->configured = $this->detectConfigured();

        if ($this->config !== [] && CacheSettings::configuredFeature('config_cache')) {
            BootstrapCacheManager::syncConfigCache($this->config);
        }
    }

    /**
     * True when app.json is loaded and project_config is set to true.
     */
    private function detectConfigured(): bool
    {
        return isset($this->config['app']['project']['project_config'])
            && $this->config['app']['project']['project_config'] === true;
    }

    /**
     * Resolve the environment name from existing IS_* constants.
     */
    private function resolveEnvironment(): string
    {
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            return 'development';
        }
        if (defined('IS_STAGING') && IS_STAGING) {
            return 'staging';
        }
        if (defined('IS_TESTING') && IS_TESTING) {
            return 'testing';
        }
        return 'production';
    }

    /**
     * Handles the secret store workflow.
     */
    public function secretStore(): ConfigSecretStore
    {
        return $this->secretStore;
    }
}
