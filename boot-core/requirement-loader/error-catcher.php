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

/**
 * error-catcher.php — Bootstrap entry point for the Catalyst error handling system.
 *
 * Loading order (intentional):
 *   1. sys-constant.php  — system constants (DS, PD, IS_CLI, NL, etc.)
 *                          Loaded outside the guard: its own LOADED_SYS_CONSTANT
 *                          guard makes it safe to require_once unconditionally.
 *   2. INITIALIZED_BUG_CATCHER guard — everything below runs exactly once:
 *      a. spl-autoload.php  — registers the SPL autoloader for bootstrap-phase
 *                             namespaces (Helpers\Exceptions, Helpers\Error, etc.)
 *                             Must run BEFORE env-constant.php so that
 *                             EnvironmentException is resolvable during .env loading.
 *      b. env-constant.php  — reads .env, defines IS_DEVELOPMENT, IS_PRODUCTION, etc.
 *      c. ErrorCatcher.php  — registers PHP error, exception and shutdown handlers.
 *
 * The INITIALIZED_BUG_CATCHER constant prevents duplicate initialization when
 * both .htaccess auto_prepend_file AND the entry point fallback load this file.
 */

require_once realpath(implode(DIRECTORY_SEPARATOR, [dirname(__FILE__), '..', 'constant', 'sys-constant.php']));

if (!defined('INITIALIZED_BUG_CATCHER')) {

    // 2a. Register SPL autoloader BEFORE env-constant.php.
    //     EnvironmentException (in Helpers\Exceptions) must be available
    //     when the .env loading throws on failure.
    $splAutoloader = implode(DS, [PD, 'boot-core', 'requirement-loader', 'spl-autoload.php']);

    if (file_exists($splAutoloader)) {
        require_once $splAutoloader;
    } else {
        $msg = NL . 'Warning: spl-autoload.php not found. Framework will not work. Solve this immediately.' . NL;
        IS_CLI ? fwrite(STDERR, $msg) : error_log($msg);
    }

    // 2b. Load environment constants (.env → PHP constants).
    require_once implode(DS, [PD, 'boot-core', 'constant', 'env-constant.php']);

    // 2b.1 Resolve JSON-backed runtime config as early as possible so the
    // rest of the framework can reuse one already-loaded ConfigManager instance.
    try {
        $GLOBALS['APP_CONFIGURATION'] = \Catalyst\Helpers\Config\ConfigManager::getInstance();
    } catch (\Throwable $e) {
        $msg = 'Configuration bootstrap error: ' . $e->getMessage();
        IS_CLI ? fwrite(STDERR, $msg . PHP_EOL) : error_log($msg);
        exit(1);
    }

    // 2c. Load the error handler coordinator.
    $errorCatcherPath = implode(DS, [PD, 'app', 'Helpers', 'Error', 'ErrorCatcher.php']);

    if (file_exists($errorCatcherPath)) {
        require_once $errorCatcherPath;
        if (!defined('INITIALIZED_BUG_CATCHER')) {
            define('INITIALIZED_BUG_CATCHER', true);
        }
    } else {
        $msg = NL . 'Warning: ErrorCatcher.php not found. Error handling will be limited.' . NL;
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            IS_CLI ? fwrite(STDERR, $msg) : error_log($msg);
        }
    }
}
