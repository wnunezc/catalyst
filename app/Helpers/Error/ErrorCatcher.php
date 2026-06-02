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

namespace Catalyst\Helpers\Error;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Class that handles capturing and displaying errors in the application.
 *
 * @package Catalyst\Helpers\Error;
 * Responsibility: Registers shutdown, exception and PHP error handlers once per request.
 */
class ErrorCatcher
{

    use SingletonTrait;

    /**
     * Flag to track if the error handling system has been initialized
     *
     * @var bool
     */
    private bool $initialized = false;

    /**
     * Initialize the error handling system.
     *
     * Responsibility: Initialize the error handling system.
     * @return void
     */
    public function initialize(): void
    {
        // Prevent double initialization
        if ($this->initialized) {
            return;
        }

        // Configure error display based on environment
        $this->configureErrorDisplay();

        // Register handlers
        register_shutdown_function([ShutdownHandler::getInstance(), 'handle']);
        set_exception_handler([ExceptionHandler::getInstance(), 'handle']);
        set_error_handler([ErrorHandler::getInstance(), 'handle']);


        // Start output buffering to capture any output before an error occurs
        if (ob_get_level() === 0) {
            ob_start();
        }

        // Mark as initialized
        $this->initialized = true;
    }

    /**
     * Check if the error handling system has been initialized
     *
     * @return bool True if initialized, false otherwise
     */
    /*public function isInitialized(): bool
    {
        return $this->initialized;
    }
    */
    /**
     * Configure PHP error display settings based on the environment.
     *
     * Responsibility: Configure PHP error display settings based on the environment.
     * @return void
     */
    private function configureErrorDisplay(): void
    {
        if (IS_DEVELOPMENT) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
        } else {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
        }
        error_reporting(E_ALL);
    }
}

// Initialize ErrorCatcher automatically when the class is loaded
// This happens when error-catcher.php loads this class via the custom autoloader
if (!defined('INITIALIZED_BUG_CATCHER')) {
    define('INITIALIZED_BUG_CATCHER', true);
}

// Initialize the error handling system
ErrorCatcher::getInstance()->initialize();
