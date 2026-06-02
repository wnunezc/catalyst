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

if (!defined('LOADED_SYS_CONSTANT')) {

    // PHP version requirement
    if (!version_compare(phpversion(), '8.4', '>=')) {
        die('This project requires PHP version 8.4 or higher');
    }

    // Default timezone — overridden by APP_TIMEZONE after env-constant.php loads
    date_default_timezone_set('UTC');

    // Directory separator
    if (!defined('DS')) {
        define('DS', DIRECTORY_SEPARATOR);
    }

    // Project root directory
    // boot-core/constant/ → boot-core/ → [project root]
    if (!defined('PD')) {
        define('PD', dirname(__DIR__, 2));
    }

    // CLI detection
    if (!defined('IS_CLI')) {
        $isCLI = defined('STDIN')
            || php_sapi_name() === 'cli'
            || (stristr(PHP_SAPI, 'cgi') && getenv('TERM'))
            || (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0);

        define('IS_CLI', $isCLI);
    }

    if (!defined('IS_REQUEST')) {
        define('IS_REQUEST', !IS_CLI);
    }

    // Terminal width — CLI only. Not defined in web context (terminal concept does not apply).
    if (!defined('TW') && IS_CLI) {
        $termWidth = null;

        if (str_contains(PHP_OS, 'WIN')) {
            $output = function_exists('shell_exec') ? shell_exec('mode con') : null;
            preg_match('/CON.*:(\n[^|]+?){3}(?<cols>\d+)/', (string) $output, $match);
            $termWidth = isset($match['cols']) ? (int) $match['cols'] : null;
        } elseif (function_exists('shell_exec')) {
            $response = shell_exec('tput cols 2>/dev/null');

            if ($response !== null) {
                $parsed = (int) trim($response);
                $termWidth = $parsed > 0 ? $parsed : null;
            }
        }

        define('TW', $termWidth ?? 80);
    }

    // New line: PHP_EOL in CLI, <br /> in web
    if (!defined('NL')) {
        define('NL', IS_CLI ? PHP_EOL : '<br />');
    }

    // Log directory
    if (!defined('LOG_DIR')) {
        define('LOG_DIR', implode(DS, [PD, 'boot-core', 'storage', 'logs']));
    }

    define('LOADED_SYS_CONSTANT', true);
}
