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
use Catalyst\Helpers\ToolBox\DrawBox;
use Closure;
use Exception;

/**
 * Class to handle output of errors caught by BugCatcher
 *
 * @package Catalyst\Helpers\Error;
 * Responsibility: Formats caught errors for CLI boxes or web error templates.
 */
class ErrorOutput
{
    use SingletonTrait;

    private const int MAX_DESCRIPTION_LENGTH = 2048;
    private const int MAX_TRACE_LENGTH = 16384;

    /**
     * Path to error templates
     */
    private const string TEMPLATE_PATH = PD . '/boot-core/template/errors/';

    private bool $renderingWebError = false;

    /**
     * Output the error information based on environment (CLI or Web).
     *
     * Responsibility: Output the error information based on environment (CLI or Web).
     * @param array $errorData Error data to display
     * @return void
     * @throws Exception
     */
    public function display(array $errorData): void
    {
        $occurredAt = microtime(true);
        $errorData['micro_time'] = $occurredAt;
        $errorData['occurred_at'] = date('Y-m-d H:i:s T', (int) $occurredAt);

        ErrorLogger::logError($errorData);

        if (IS_CLI) {
            $this->displayCLI($errorData);
        } else {
            $this->displayWeb($errorData);
        }
    }

    /**
     * Display error information in CLI mode.
     *
     * Responsibility: Display error information in CLI mode.
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
     * Format error data for CLI output.
     *
     * Responsibility: Format error data for CLI output.
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
     * Display error information in web mode using templates.
     *
     * Responsibility: Display error information in web mode using templates.
     * @param array $errorData Error data to display
     * @return void
     */
    private function displayWeb(array $errorData): void
    {
        if ($this->renderingWebError) {
            echo $this->fallbackHtml([
                'class' => 'ErrorHandler',
                'type' => 'Recursive error rendering failure',
                'description' => 'The error response could not be rendered safely.',
                'ticket' => $errorData['ticket'] ?? $errorData['micro_time'] ?? '',
                'occurred_at' => $errorData['occurred_at'] ?? '',
            ]);
            return;
        }

        $this->renderingWebError = true;

        try {
            $templateName = IS_DEVELOPMENT ? 'handler_error' : 'handler_error_no';
            $templatePath = $this->resolveTemplatePath($templateName);
            $safeErrorData = $this->boundedErrorData($errorData);
            $source = '';

            if (IS_DEVELOPMENT && $safeErrorData['file'] !== '' && $safeErrorData['line'] > 0) {
                $source = $this->getCodeSnippet($safeErrorData['file'], $safeErrorData['line']);
            }

            if ($templatePath !== null && file_exists($templatePath)) {
                extract(['errorArray' => $safeErrorData, 'source' => $source]);
                include $templatePath;
                return;
            }

            echo $this->fallbackHtml($safeErrorData, $source);
        } catch (\Throwable) {
            echo $this->fallbackHtml([
                'class' => 'ErrorHandler',
                'type' => 'Error rendering failure',
                'description' => 'The original error was logged, but its response could not be rendered.',
                'ticket' => $errorData['ticket'] ?? $errorData['micro_time'] ?? '',
                'occurred_at' => $errorData['occurred_at'] ?? '',
            ]);
        } finally {
            $this->renderingWebError = false;
        }
    }

    /**
     * Builds the dependency-free response used when bootstrap templates are unavailable.
     *
     * @param array<string, mixed> $errorData
     */
    private function fallbackHtml(array $errorData, string $source = ''): string
    {
        $class = $this->escape((string) ($errorData['class'] ?? 'Error'));
        $type = $this->escape((string) ($errorData['type'] ?? 'Unhandled error'));
        $description = $this->escape((string) ($errorData['description'] ?? 'An unexpected error occurred.'));
        $ticket = $this->escape((string) ($errorData['ticket'] ?? $errorData['micro_time'] ?? ''));
        $occurredAt = $this->escape((string) ($errorData['occurred_at'] ?? ''));
        $file = $this->escape((string) ($errorData['file'] ?? ''));
        $line = (int) ($errorData['line'] ?? 0);
        $trace = $this->escape((string) ($errorData['trace_msg'] ?? ''));
        $details = '';

        if (IS_DEVELOPMENT) {
            $location = $file !== '' ? '<p><strong>Location:</strong> <code>' . $file . ':' . $line . '</code></p>' : '';
            $traceHtml = $trace !== '' ? '<h2>Trace</h2><pre><code>' . $trace . '</code></pre>' : '';
            $sourceHtml = $source !== '' ? '<h2>Source</h2>' . $source : '';
            $details = '<p><strong>Class:</strong> ' . $class . '</p>'
                . '<p><strong>Type:</strong> ' . $type . '</p>'
                . $location
                . $traceHtml
                . $sourceHtml;
        }

        $ticketHtml = $ticket !== '' ? '<p><strong>Error ticket:</strong> <code>' . $ticket . '</code></p>' : '';
        $occurredAtHtml = $occurredAt !== ''
            ? '<p><strong>Occurred at:</strong> <time>' . $occurredAt . '</time></p>'
            : '';

        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            . '<title>Catalyst error</title></head><body><main>'
            . '<h1>Catalyst error</h1><p>' . $description . '</p>'
            . $ticketHtml . $occurredAtHtml . $details
            . '</main></body></html>';
    }

    /**
     * Keeps only scalar diagnostic fields and caps their rendered size.
     *
     * @param array<string, mixed> $errorData
     * @return array<string, mixed>
     */
    private function boundedErrorData(array $errorData): array
    {
        return [
            'class' => $this->boundedString($errorData['class'] ?? 'Error', 256),
            'type' => $this->boundedString($errorData['type'] ?? 'Unhandled error', 256),
            'description' => $this->boundedString(
                $errorData['description'] ?? 'An unexpected error occurred.',
                self::MAX_DESCRIPTION_LENGTH
            ),
            'file' => $this->boundedString($errorData['file'] ?? '', 1024),
            'line' => max(0, (int) ($errorData['line'] ?? 0)),
            'trace_msg' => $this->boundedString($errorData['trace_msg'] ?? '', self::MAX_TRACE_LENGTH),
            'ticket' => $this->boundedString(
                $errorData['ticket'] ?? $errorData['micro_time'] ?? '',
                128
            ),
            'occurred_at' => $this->boundedString($errorData['occurred_at'] ?? '', 128),
            'micro_time' => $this->boundedString($errorData['micro_time'] ?? '', 128),
        ];
    }

    private function boundedString(mixed $value, int $maximumLength): string
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return '';
        }

        $string = (string) $value;
        if (strlen($string) <= $maximumLength) {
            return $string;
        }

        return substr($string, 0, $maximumLength) . "\n[truncated]";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Resolves the first available error template path for a template name.
     *
     * Responsibility: Resolves the first available error template path for a template name.
     */
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
     * Get a code snippet from the file where the error occurred.
     *
     * Responsibility: Get a code snippet from the file where the error occurred.
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
     * Generate formatted backtrace.
     *
     * Responsibility: Generate formatted backtrace.
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
     * Responsibility: Format arguments for display.
     * @param array $args Arguments array.
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
     * Responsibility: Get a description of the route (file and line) or magic call method.
     * @param array $track Stack trace information.
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
