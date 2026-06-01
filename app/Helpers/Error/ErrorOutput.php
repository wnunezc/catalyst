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

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\ToolBox\DrawBox;
use Closure;
use Exception;

/**
 * Class to handle output of errors caught by BugCatcher
 *
 * @package Catalyst\Helpers\Error;
 */
class ErrorOutput
{
    use SingletonTrait;

    /**
     * Path to error templates
     */
    private const string TEMPLATE_PATH = PD . '/boot-core/template/errors/';

    /**
     * Output the error information based on environment (CLI or Web)
     *
     * @param array $errorData Error data to display
     * @return void
     * @throws Exception
     */
    public function display(array $errorData): void
    {
        $errorData['micro_time'] = microtime(true);

        ErrorLogger::logError($errorData);

        if (IS_CLI) {
            $this->displayCLI($errorData);
        } else {
            $this->displayWeb($errorData);
        }
    }

    /**
     * Display error information in CLI mode
     *
     * @param array $errorData Error data to display
     * @return void
     * @throws Exception
     */
    private function displayCLI(array $errorData): void
    {
        $output = $this->formatCliOutput($errorData);
        $drawBox = DrawBox::getInstance();

        // Display formatted error output using DrawBox with error styling (red, highlighted)
        echo $drawBox->draw($output, [
            'headerLines' => 1,
            'footerLines' => 0,
            'highlight' => true,
            'maxWidth' => 0,
            'style' => 2, // Error style (red)
            'isError' => true
        ]);
    }

    /**
     * Format error data for CLI output
     *
     * @param array $errorData Error data to format
     * @return string Formatted error information
     */
    private function formatCliOutput(array $errorData): string
    {
        $output = '';

        if (IS_DEVELOPMENT) {
            $output .= "Class: {$errorData['class']}" . NL
                . "Description:" . NL . "{$errorData['description']}" . NL . NL
                . "File: {$errorData['file']}" . NL
                . "Line: {$errorData['line']}" . ' ' . "Type: {$errorData['type']}" . ' ' . "Time: {$errorData['micro_time']}" . NL . NL
                . "Backtrace:" . NL . "{$errorData['trace_msg']}" . NL;
        } else {
            $output .= "Micro Time: {$errorData['micro_time']}";
        }

        return $output;
    }

    /**
     * Display error information in web mode using templates
     *
     * @param array $errorData Error data to display
     * @return void
     */
    private function displayWeb(array $errorData): void
    {
        // Determine which template to use based on the environment
        $templateName = IS_DEVELOPMENT ? 'handler_error' : 'handler_error_no';
        $templatePath = $this->resolveTemplatePath($templateName);

        // Generate source code view for development mode
        $source = '';
        if (IS_DEVELOPMENT && isset($errorData['file']) && isset($errorData['line'])) {
            $source = $this->getCodeSnippet($errorData['file'], $errorData['line']);
        }

        // Extract error data to make them available in the template
        extract(['errorArray' => $errorData, 'source' => $source]);

        // Render the template
        if ($templatePath !== null && file_exists($templatePath)) {
            include $templatePath;
        } else {
            // Fallback if the template is missing
            echo '<h1>Error</h1>';
            echo '<p>Error template is not found for: ' . htmlspecialchars($templateName) . '</p>';
            if (IS_DEVELOPMENT) {
                echo '<pre>' . print_r($errorData, true) . '</pre>';
            }
        }
    }

    private function resolveTemplatePath(string $templateName): ?string
    {
        foreach (['phtml', 'php'] as $extension) {
            $candidate = self::TEMPLATE_PATH . $templateName . '.' . $extension;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Get a code snippet from the file where the error occurred
     *
     * @param string $file File path
     * @param int $line Line number
     * @param int $contextLines Number of lines to show before and after the error line
     * @return string HTML formatted code snippet
     */
    private function getCodeSnippet(string $file, int $line, int $contextLines = 5): string
    {
        if (!file_exists($file) || !is_readable($file)) {
            return '<pre>Source code not available.</pre>';
        }

        $fileContent = file($file);
        if (!$fileContent) {
            return '<pre>Unable to read source file.</pre>';
        }

        $startLine = max(0, $line - $contextLines - 1);
        $endLine = min(count($fileContent) - 1, $line + $contextLines - 1);

        $html = '<pre><code>';
        for ($i = $startLine; $i <= $endLine; $i++) {
            $lineNumber = $i + 1;
            $codeLine = htmlspecialchars($fileContent[$i]);

            // Highlight the error line
            if ($lineNumber == $line) {
                $html .= '<span class="error-source-line error-source-line--highlight">';
                $html .= "$lineNumber: $codeLine</span>";
            } else {
                $html .= "$lineNumber: $codeLine";
            }
        }
        $html .= '</code></pre>';

        return $html;
    }

    /**
     * Generate formatted backtrace
     *
     * @param array $errorData Error data containing trace information
     * @return string Formatted backtrace string
     */
    public function formatBacktrace(array $errorData): string
    {
        $backtraceMessage = [];
        $traceData = $errorData['trace'] ?? [];

        if (!empty($traceData)) {
            foreach ($traceData as $track) {

                $args = '';

                if (isset($track['args']) && !empty($track['args'])) {
                    $args = $this->formatArguments($track['args']);
                }

                $route = $this->getRouteDescription($track);
                $backtraceMessage[] = sprintf('%s%s(%s)', $route, $track['function'], $args);
            }
        } else {
            $backtraceMessage[] = sprintf('No backtrace data in the %s.', $errorData['class']);
        }

        return implode(NL, $backtraceMessage);
    }

    /**
     * Format arguments for display.
     *
     * @param array $args Arguments array.
     *
     * @return string Formatted arguments.
     */
    private function formatArguments(array $args): string
    {
        $formattedArgs = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $formattedArgs[] = 'Array';
            } elseif (is_object($arg)) {
                if ($arg instanceof Closure) {
                    $formattedArgs[] = 'Closure';
                } else {
                    $formattedArgs[] = get_class($arg);
                }
            } else {
                $formattedArgs[] = is_string($arg) ? "'" . $arg . "'" : (string)$arg;
            }
        }

        return implode(',', $formattedArgs);
    }

    /**
     * Get a description of the route (file and line) or magic call method.
     *
     * @param array $track Stack trace information.
     *
     * @return string Route description.
     */
    private function getRouteDescription(array $track): string
    {
        if (!isset($track['file']) && !isset($track['line'])) {
            return sprintf('Magic Call Method: (%s)->', $track['class']);
        }

        return sprintf('%s %s calling Method: ', $track['file'], $track['line']);
    }
}
