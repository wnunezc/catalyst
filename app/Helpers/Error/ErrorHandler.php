<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
 *
 */

namespace Catalyst\Helpers\Error;

use Exception;
use Catalyst\Framework\Traits\{SingletonTrait, OutputCleanerTrait, ErrorTypeTrait};

/**
 * Class that handles registered Errors.
 *
 * @package Catalyst\Helpers\Error;
 */
class ErrorHandler
{
    use SingletonTrait;
    use OutputCleanerTrait;
    use ErrorTypeTrait;

    /**
     * Error handler. Captures and handles errors generated in the application.
     *
     * @param int $errorLevel Error level.
     * @param string $errorDesc Error description.
     * @param string $errorFile File where the error occurred.
     * @param int $errorLine Line where the error occurred.
     * @return bool True to prevent default PHP error handler
     * @throws Exception
     */
    public function handle(int $errorLevel, string $errorDesc, string $errorFile, int $errorLine): bool
    {
        // Only handle errors that match the error_reporting level
        if (!(error_reporting() & $errorLevel)) {
            return false;
        }

        // Clean any output already sent
        $this->cleanOutput();

        // Map error level to text description
        $errorType = $this->getErrorType($errorLevel);

        $trace = array_reverse(debug_backtrace());
        array_pop($trace);
        $trace = array_reverse($trace);

        // Prepare error data
        $errorArray = [
            'class' => 'ErrorHandler',
            'type' => $errorType,
            'description' => $errorDesc,
            'file' => $errorFile,
            'line' => $errorLine,
            'trace' => $trace,
        ];

        $bug_output = ErrorOutput::getInstance();

        // Generate backtrace
        $errorArray['trace_msg'] = $bug_output->formatBacktrace($errorArray);

        // Display error
        $bug_output->display($errorArray);

        // Return true to prevent default error handler
        return true;
    }

}