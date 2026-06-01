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

namespace Catalyst\Helpers\ToolBox;

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\IO\FileOutput;
use Exception;

/**
 * DrawBox class for creating formatted text boxes in terminal or HTML
 *
 * @package Catalyst\Helpers\ToolBox;
 */
class DrawBox
{
    use SingletonTrait;

    /**
     * @var array Box characters for drawing
     */
    private array $boxChars = [
        'tl' => '+', // top left
        'tr' => '+', // top right
        'bl' => '+', // bottom left
        'br' => '+', // bottom right
        'v' => '|', // vertical line
        'h' => '=', // horizontal line
        'hs' => '-', // horizontal separator line
        'ls' => '╟', // left separator
        'rs' => '╢', // right separator
    ];

    private FileOutput $fileOutput;

    private DrawBoxCliRenderer $cliRenderer;

    private DrawBoxHtmlRenderer $htmlRenderer;

    private DrawBoxFileOutputDecorator $fileOutputDecorator;

    /**
     * DrawBox constructor
     */
    protected function __construct()
    {
        $this->fileOutput = FileOutput::getInstance();
        $stylePalette = new DrawBoxStylePalette();
        $textHelper = new DrawBoxTextHelper($this->fileOutput);
        $this->cliRenderer = new DrawBoxCliRenderer($this->boxChars, $stylePalette, $textHelper, $this->fileOutput);
        $this->htmlRenderer = new DrawBoxHtmlRenderer($stylePalette);
        $this->fileOutputDecorator = new DrawBoxFileOutputDecorator($this->boxChars);
    }

    /**
     * Draw a box around the given content
     *
     * @param array|string $content Content to place inside the box
     * @param array $options Box options and styling
     * @return string Formatted box as string
     * @throws Exception
     */
    public function draw(array|string $content, array $options = []): string
    {
        $options = $this->normalizeOptions($options);
        $contentLines = $this->normalizeContent($content);

        if ($options['htmlOutput'] || (!$this->isCli() && !defined('FORCE_CLI_OUTPUT'))) {
            return $this->htmlRenderer->render($contentLines, $options);
        }

        $boxOutput = $this->cliRenderer->render($contentLines, $options);

        if ($options['enableFileOutput'] && !$options['isError'] && $this->fileOutput->isFileOutputRequested()) {
            $result = $this->fileOutput->handleFileOutput($boxOutput);
            if ($result['success'] || $result['filename']) {
                $boxOutput = $this->fileOutputDecorator->append($boxOutput, $result);
            }
        }

        return $boxOutput;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        return array_merge([
            'headerLines' => 0,
            'footerLines' => 0,
            'highlight' => false,
            'maxWidth' => 0,
            'style' => 0,
            'isError' => false,
            'htmlOutput' => false,
            'enableFileOutput' => true,
        ], $options);
    }

    /**
     * @return string[]
     */
    private function normalizeContent(array|string $content): array
    {
        return is_array($content) ? $content : (preg_split('/\r\n|\r|\n/', rtrim($content)) ?: []);
    }

    /**
     * Check if running in the CLI environment
     *
     * @return bool True if in CLI
     */
    private function isCli(): bool
    {
        return defined('IS_CLI') ? IS_CLI : (
            defined('STDIN')
            || php_sapi_name() === 'cli'
            || (stristr(PHP_SAPI, 'cgi') && getenv('TERM'))
            || (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv'] ?? []) > 0)
        );
    }
}
