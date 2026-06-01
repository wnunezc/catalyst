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
 * Class that handles registered Shutdowns.
 *
 * @package Catalyst\Helpers\Error;
 */
class ShutdownHandler
{
    use SingletonTrait;
    use OutputCleanerTrait;
    use ErrorTypeTrait;

    /**
     * Shutdown handler. Captures fatal errors that would otherwise not be caught.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {

        // Get the last error
        $error = error_get_last();

        // Only handle fatal errors — non-fatal types are already handled by ErrorHandler.
        // error_get_last() returns the last error regardless of severity, so without
        // this guard the ShutdownHandler would re-display notices/warnings already shown.
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];

        if ($error !== null && in_array($error['type'], $fatalTypes, true)) {

            $this->cleanOutput();

            $trace = array_reverse(debug_backtrace());
            array_pop($trace);

            $errorArray = [
                'class' => 'ShutdownHandler',
                'type' => $this->getErrorType($error['type']),
                'description' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'trace' => $trace,
            ];

            $bug_output = ErrorOutput::getInstance();

            // Generate backtrace
            $errorArray['trace_msg'] = $bug_output->formatBacktrace($errorArray);

            // Display error
            $bug_output->display($errorArray);
        }
    }

}