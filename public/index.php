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
 * Fallback mechanism: Load error-catcher if not already loaded by server config
 *
 * This ensures critical constants and error handling are available before Composer autoload.
 * The error-catcher.php file loads sys-constant.php and env-constant.php which define
 * essential constants like IS_CLI, NL, DS, PD, etc.
 *
 * Normally, .htaccess (Apache) or .user.ini (Nginx/CGI) would load this via auto_prepend_file,
 * but if the server doesn't support or read those configurations, this fallback ensures
 * the application still initializes correctly.
 *
 * The INITIALIZED_BUG_CATCHER constant prevents double initialization if already loaded.
 */
if (!defined('INITIALIZED_BUG_CATCHER')) {
    $errorCatcherPath = __DIR__ . '/../boot-core/requirement-loader/error-catcher.php';
    if (file_exists($errorCatcherPath)) {
        require_once $errorCatcherPath;
    } else {
        // Critical failure - cannot continue without error handling system
        die('CRITICAL ERROR: Error handling system not found at expected path. Application cannot start.');
    }
}

/**
 * Prevent web entry point execution in CLI environment
 */
if (IS_CLI) {
    echo '╔══════════════════════════════════════════════════════════════════════╗' . NL;
    echo '║ Execution stopped!!!                                                 ║' . NL;
    echo '╟──────────────────────────────────────────────────────────────────────╢' . NL;
    echo '║ This is the WEB entry point and cannot be executed from CLI.         ║' . NL;
    echo '║                                                                      ║' . NL;
    echo '║ To run the project in CLI, use "cli.php" as the entry point:         ║' . NL;
    echo '║   php ./public/cli.php                                               ║' . NL;
    echo '║   php ./public/cli.php -h    (for help)                              ║' . NL;
    echo '╚══════════════════════════════════════════════════════════════════════╝' . NL;
    exit(1);
}

/**
 * Load Composer autoloader
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Catalyst\Kernel;

Kernel::getInstance()->bootstrap()->run();