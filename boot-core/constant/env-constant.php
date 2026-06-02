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

use Catalyst\Helpers\Exceptions\EnvironmentException;

if (!defined('LOADED_ENV_CONSTANT')) {

    /**
     * Reads and parses variables from the .env file.
     *
     * Rules:
     * - Lines starting with # are ignored (comments)
     * - Lines without = are ignored
     * - Quoted values ("..." or '...'): content preserved as-is, quotes stripped
     * - Unquoted values: inline comments (anything after " #") are stripped
     * - Values containing commas are converted to arrays
     * - Duplicate keys are ignored (first occurrence wins)
     *
     * @return array<string, string|string[]>
     * @throws EnvironmentException when the .env file is missing, unreadable or empty
     */
    function readEnvironmentVariable(): array
    {
        $envPath     = implode(DS, [PD, 'boot-core', 'config', 'env', '.env']);
        $examplePath = implode(DS, [PD, 'boot-core', 'config', 'env', '.env.example']);

        if (!file_exists($envPath)) {
            if (!file_exists($examplePath)) {
                throw EnvironmentException::fileMissing($examplePath);
            }
            if (!copy($examplePath, $envPath)) {
                throw EnvironmentException::copyFailed($examplePath, $envPath);
            }
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw EnvironmentException::unreadable($envPath);
        }

        if (empty($lines)) {
            throw EnvironmentException::empty($envPath);
        }

        $envArray = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Quoted values: strip surrounding quotes, preserve content verbatim
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            } else {
                // Unquoted values: strip inline comments (e.g. value # comment → value)
                $commentPos = strpos($value, ' #');
                if ($commentPos !== false) {
                    $value = trim(substr($value, 0, $commentPos));
                }
            }

            // CSV values: KEY=a,b,c → ['a', 'b', 'c']
            if (str_contains($value, ',')) {
                $value = array_map('trim', explode(',', $value));
            }

            // First occurrence wins
            if (!isset($envArray[$key])) {
                $envArray[$key] = $value;
            }
        }

        return $envArray;
    }

    // -------------------------------------------------------------------------
    // Bootstrap: load .env or halt
    // -------------------------------------------------------------------------

    try {
        $envArray = readEnvironmentVariable();
        define('ENV', true);
    } catch (EnvironmentException $e) {
        define('ENV', false);
        $msg = 'Environment Error: ' . $e->getMessage();
        IS_CLI ? fwrite(STDERR, $msg . PHP_EOL) : error_log($msg);
        exit(1);
    }

    // Override timezone with .env value (sys-constant.php sets 'UTC' as default)
    date_default_timezone_set($envArray['APP_TIMEZONE'] ?? 'UTC');

    // -------------------------------------------------------------------------
    // APP_ENV validation
    // -------------------------------------------------------------------------

    /** @var string[] Valid environment identifiers accepted by the framework */
    $validEnvironments = ['development', 'staging', 'testing', 'production'];
    $currentEnv        = strtolower((string)($envArray['APP_ENV'] ?? 'production'));

    if (!in_array($currentEnv, $validEnvironments, true)) {
        $validList  = implode(', ', $validEnvironments);
        $msg        = "Invalid APP_ENV value '$currentEnv'. Falling back to 'production'. Allowed: $validList";
        IS_CLI ? fwrite(STDERR, $msg . PHP_EOL) : error_log($msg);
        $currentEnv = 'production';
    }

    // -------------------------------------------------------------------------
    // Environment constants
    // -------------------------------------------------------------------------

    if (!defined('IS_DEVELOPMENT')) {
        define('IS_DEVELOPMENT', $currentEnv === 'development');
    }

    if (!defined('IS_STAGING')) {
        define('IS_STAGING', $currentEnv === 'staging');
    }

    if (!defined('IS_TESTING')) {
        define('IS_TESTING', $currentEnv === 'testing');
    }

    if (!defined('IS_PRODUCTION')) {
        define('IS_PRODUCTION', $currentEnv === 'production');
    }

    if (!defined('GET_ENV_VAR')) {
        define('GET_ENV_VAR', $envArray);
    }

    if (!defined('IS_CONFIGURED')) {
        /**
         * Indicates whether the Setup Wizard has completed for the active
         * environment. Falls back to false when app.json is absent or invalid.
         */
        $appConfigFile = implode(DS, [PD, 'boot-core', 'config', $currentEnv, 'app.json']);
        $isConfigured  = false;

        if (is_file($appConfigFile)) {
            $appConfigRaw = file_get_contents($appConfigFile);
            $appConfig    = $appConfigRaw !== false ? json_decode($appConfigRaw, true) : null;

            if (is_array($appConfig)) {
                $isConfigured = ($appConfig['project']['project_config'] ?? false) === true;
            } elseif (json_last_error() !== JSON_ERROR_NONE) {
                $msg = "Invalid app.json while resolving IS_CONFIGURED: " . json_last_error_msg();
                IS_CLI ? fwrite(STDERR, $msg . PHP_EOL) : error_log($msg);
            }
        }

        define('IS_CONFIGURED', $isConfigured);
    }

    if (!defined('DISPLAY_LOGS')) {
        /**
         * Whether the Logger should output logs to screen (development tool).
         * Controlled via APP_DISPLAY_LOGS in .env.
         */
        define('DISPLAY_LOGS', ($envArray['APP_DISPLAY_LOGS'] ?? 'true') === 'true');
    }

    if (!defined('ROUTE_CACHE')) {
        /**
         * Deprecated compatibility constant.
         * Runtime cache activation is resolved exclusively from
         * boot-core/config/{environment}/cache.json and only consumed in production.
         */
        define('ROUTE_CACHE', false);
    }

    // -------------------------------------------------------------------------
    // Database fallback constants (first-boot only)
    // Primary source: boot-core/config/{environment}/db.json (Setup Wizard)
    // These constants are used by DatabaseManager when the JSON file is absent.
    // -------------------------------------------------------------------------

    if (!defined('DB_HOST')) {
        define('DB_HOST', (string)($envArray['DB_HOST'] ?? 'localhost'));
    }

    if (!defined('DB_PORT')) {
        define('DB_PORT', (int)($envArray['DB_PORT'] ?? 3306));
    }

    if (!defined('DB_DATABASE')) {
        define('DB_DATABASE', (string)($envArray['DB_DATABASE'] ?? ''));
    }

    if (!defined('DB_USERNAME')) {
        define('DB_USERNAME', (string)($envArray['DB_USERNAME'] ?? ''));
    }

    if (!defined('DB_PASSWORD')) {
        define('DB_PASSWORD', (string)($envArray['DB_PASSWORD'] ?? ''));
    }

    define('LOADED_ENV_CONSTANT', true);
}
