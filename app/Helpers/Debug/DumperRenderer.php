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

namespace Catalyst\Helpers\Debug;

use Throwable;

/**
 * DumperRenderer class for rendering debug output
 *
 * This class is responsible for rendering the debug output in different formats,
 * such as HTML with modal or CLI. It handles the visual presentation of the data.
 *
 * @package Catalyst\Helpers\Debug;
 * Responsibility: Renders formatted dump data as terminal text or interactive HTML output.
 */
class DumperRenderer
{
    /**
     * DumperConfig instance
     */
    private DumperConfig $config;

    /**
     * DumperColorizer instance
     */
    private DumperColorizer $colorizer;

    /**
     * DumperCollapsible instance
     */
    private DumperCollapsible $collapsible;

    /**
     * Counter for generating unique IDs for dump modals
     */
    private static int $dumpCounter = 0;
    
    /**
     * Base path for template files
     */
    private string $templatePath;

    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param DumperConfig $config Configuration instance
     * @param DumperColorizer $colorizer Colorizer instance
     * @param DumperCollapsible $collapsible Collapsible instance
     */
    public function __construct(
        DumperConfig      $config,
        DumperColorizer   $colorizer,
        DumperCollapsible $collapsible
    )
    {
        $this->config = $config;
        $this->colorizer = $colorizer;
        $this->collapsible = $collapsible;

        $this->templatePath = implode(DS, [PD, 'boot-core', 'template', 'debug']);
    }
    
    /**
     * Load a template file and extract variables into its scope.
     *
     * Responsibility: Load a template file and extract variables into its scope.
     * @param string $templateName Template file name
     * @param array $variables Variables to extract into template scope
     * @return string Rendered template content
     */
    private function loadTemplate(string $templateName, array $variables = []): string
    {
        $templateFile = $this->resolveTemplatePath($templateName);

        if ($templateFile === null || !file_exists($templateFile)) {
            $error = "Template file not found: $templateName";
            error_log($error);
            return '<!-- ERROR: ' . $error . ' -->';
        }

        try {
            ob_start();
            extract($variables);
            include $templateFile;
            return ob_get_clean();
        } catch (Throwable $e) {
            $error = "Error loading template $templateName: " . $e->getMessage();
            error_log($error);
            return '<!-- ERROR: ' . $error . ' -->';
        }
    }

    /**
     * Resolve the first existing template path for the requested template name.
     *
     * Responsibility: Resolve the first existing template path for the requested template name.
     * @param string $templateName Template file name
     * @return string|null Existing template path or null when no candidate exists
     */
    private function resolveTemplatePath(string $templateName): ?string
    {
        $candidates = [$templateName];

        if (str_ends_with($templateName, '.phtml')) {
            $candidates[] = substr($templateName, 0, -6) . '.php';
        }

        foreach ($candidates as $candidate) {
            $templateFile = implode(DS, [$this->templatePath, $candidate]);
            if (file_exists($templateFile)) {
                return $templateFile;
            }
        }

        return null;
    }

    /**
     * Render debug output.
     *
     * Responsibility: Render debug output.
     * @param array $data Array of variables to dump
     * @param array|null $caller Caller information (file, line)
     * @param bool $isHtml Whether to render as HTML
     * @return string Rendered output
     */
    public function render(array $data, ?array $caller, bool $isHtml): string
    {
        if (empty($data)) {
            return '';
        }

        if (!$isHtml) {
            return $this->renderCli($data, $caller);
        }

        return $this->renderHtml($data, $caller);
    }

    /**
     * Render debug output for CLI.
     *
     * Responsibility: Render debug output for CLI.
     * @param array $data Array of variables to dump
     * @param array|null $caller Caller information (file, line)
     * @return string Rendered CLI output
     */
    private function renderCli(array $data, ?array $caller): string
    {
        $output = '';
        // Get terminal width or default to 80
        $terminalWidth = 80;
        if (defined('TW')) {
            $terminalWidth = TW;
        } elseif (function_exists('exec')) {
            $nullDevice = defined('SHELL_NULL_DEVICE')
                ? SHELL_NULL_DEVICE
                : (PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null');
            @exec('tput cols 2>' . $nullDevice, $columns);
            if (!empty($columns[0])) {
                $terminalWidth = (int)$columns[0];
            }
        }
        $width = min(120, $terminalWidth);

        // Display caller information if available
        if ($caller) {
            $callerText = "Called from: " . $caller['file'] . " (line " . $caller['line'] . ")";
            $output .= str_repeat('=', $width) . PHP_EOL;
            $output .= "\033[1;36m" . $callerText . "\033[0m" . PHP_EOL;
            $output .= str_repeat('=', $width) . PHP_EOL;
        }

        // Reset collapse counter for each dump call
        $this->collapsible->resetCounter();

        foreach ($data as $var) {
            $output .= $var . PHP_EOL;
            $output .= str_repeat('-', $width) . PHP_EOL;
        }

        return $output;
    }

    /**
     * Render debug output for HTML.
     *
     * Responsibility: Render debug output for HTML.
     * @param array $data Array of variables to dump
     * @param array|null $caller Caller information (file, line)
     * @return string Rendered HTML output
     */
    private function renderHtml(array $data, ?array $caller): string
    {
        // Create a modal
        $dumpId = 'catalyst-dump-' . (++self::$dumpCounter);
        $modalId = $dumpId . '-modal';
        $btnId = $dumpId . '-btn';

        $output = $this->generateCss($dumpId);
        $output .= $this->generateJavaScript($dumpId, $modalId);
        $output .= $this->generateModal($dumpId, $modalId, $data, $caller);

        if ($this->config->getShowFloatingButton()) {
            $output .= $this->generateFloatingButton($dumpId, $btnId, $data);
        }

        return $output;
    }

    /**
     * Generate CSS for HTML output.
     *
     * Responsibility: Generate CSS for HTML output.
     * @param string $dumpId Unique ID for this dump
     * @return string CSS code
     */
    private function generateCss(string $dumpId): string
    {
        $bgColor = $this->colorizer->getBackgroundColor();
        $textColor = $this->colorizer->getTextColor();
        $headerColor = $this->colorizer->getHeaderColor();
        $labelColor = $this->colorizer->getColor('label', true);
        $headerColorBrighter = $this->adjustBrightness($headerColor, 20);
        $themeColors = $this->colorizer->getHtmlColors();
        $dumpCounter = self::$dumpCounter;
        $nonce = class_exists(\Catalyst\Helpers\Security\CspNonce::class)
            ? \Catalyst\Helpers\Security\CspNonce::get()
            : '';
        $nonceAttr = $nonce !== ''
            ? ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"'
            : '';
        
        // Prepare variables for the template
        $variables = [
            'dumpId' => $dumpId,
            'bgColor' => $bgColor,
            'textColor' => $textColor,
            'headerColor' => $headerColor,
            'labelColor' => $labelColor,
            'headerColorBrighter' => $headerColorBrighter,
            'themeColors' => $themeColors,
            'nonceAttr' => $nonceAttr,
            'dumpCounter' => $dumpCounter
        ];
        
        // Load and render the template
        return $this->loadTemplate('dumper-styles.tpl.phtml', $variables);
    }

    /**
     * Generate JavaScript for HTML output.
     *
     * Responsibility: Generate JavaScript for HTML output.
     * @param string $dumpId Unique ID for this dump
     * @param string $modalId Modal ID
     * @return string JavaScript code
     */
    private function generateJavaScript(string $dumpId, string $modalId): string
    {
        $collapsibleJs = $this->collapsible->getJavaScript();
        $dumpCounter = self::$dumpCounter;
        
        // Prepare variables for the template
        $variables = [
            'dumpId' => $dumpId,
            'modalId' => $modalId,
            'collapsibleJs' => $collapsibleJs,
            'dumpCounter' => $dumpCounter
        ];
        
        // Load and render the template
        return $this->loadTemplate('dumper-scripts.tpl.phtml', $variables);
    }

    /**
     * Generate modal HTML.
     *
     * Responsibility: Generate modal HTML.
     * @param string $dumpId Unique ID for this dump
     * @param string $modalId Modal ID
     * @param array $data Array of formatted variables
     * @param array|null $caller Caller information
     * @return string Modal HTML
     */
    private function generateModal(string $dumpId, string $modalId, array $data, ?array $caller): string
    {
        $bgColor = $this->colorizer->getBackgroundColor();
        $textColor = $this->colorizer->getTextColor();
        $labelColor = $this->colorizer->getColor('label', true);
        $dumpCounter = self::$dumpCounter;
        
        // Prepare caller text if available
        $callerText = '';
        if ($caller) {
            $callerText = "Called from: " . $caller['file'] . " (line " . $caller['line'] . ")";
        }
        
        // Prepare variables for the template
        $variables = [
            'dumpId' => $dumpId,
            'modalId' => $modalId,
            'data' => $data,
            'caller' => $caller,
            'callerText' => $callerText,
            'bgColor' => $bgColor,
            'textColor' => $textColor,
            'labelColor' => $labelColor,
            'dumpCounter' => $dumpCounter
        ];
        
        // Load and render the template
        return $this->loadTemplate('dumper-modal.tpl.phtml', $variables);
    }

    /**
     * Generate floating button HTML.
     *
     * Responsibility: Generate floating button HTML.
     * @param string $dumpId Unique ID for this dump
     * @param string $btnId Button ID
     * @param array $data Array of variables
     * @return string Button HTML
     */
    private function generateFloatingButton(string $dumpId, string $btnId, array $data): string
    {
        $dumpCounter = self::$dumpCounter;
        
        // Prepare variables for the template
        $variables = [
            'dumpId' => $dumpId,
            'btnId' => $btnId,
            'data' => $data,
            'dumpCounter' => $dumpCounter
        ];
        
        // Load and render the template
        return $this->loadTemplate('dumper-button.tpl.phtml', $variables);
    }

    /**
     * Adjust brightness of a hex color.
     *
     * Responsibility: Adjust brightness of a hex color.
     * @param string $hexColor Hex color code
     * @param int $percent Percentage to adjust (-100 to 100)
     * @return string Adjusted hex color
     */
    private function adjustBrightness(string $hexColor, int $percent): string
    {
        // Remove # if present
        $hex = ltrim($hexColor, '#');

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust brightness
        $r = max(0, min(255, $r + $percent));
        $g = max(0, min(255, $g + $percent));
        $b = max(0, min(255, $b + $percent));

        // Convert back to hex
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
