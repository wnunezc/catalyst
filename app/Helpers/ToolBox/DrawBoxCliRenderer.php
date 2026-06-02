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

namespace Catalyst\Helpers\ToolBox;

use Catalyst\Helpers\IO\FileOutput;

/**
 * Renders draw-box content for terminal output.
 *
 * @package Catalyst\Helpers\ToolBox
 * Responsibility: Calculates terminal dimensions, wraps content and assembles styled CLI boxes.
 */
final class DrawBoxCliRenderer
{
    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param array<string, string> $boxChars
     */
    public function __construct(
        private readonly array $boxChars,
        private readonly DrawBoxStylePalette $stylePalette,
        private readonly DrawBoxTextHelper $textHelper,
        private readonly FileOutput $fileOutput,
    ) {
    }

    /**
     * Renders content lines into a terminal box or a width warning.
     *
     * Responsibility: Renders content lines into a terminal box or a width warning.
     * @param string[] $content
     * @param array<string, mixed> $options
     */
    public function render(array $content, array $options): string
    {
        $colorScheme = $this->stylePalette->getCliScheme((int) $options['style']);
        $maxContentWidth = $this->getMaxContentWidth($content);
        $termWidth = $this->getTerminalWidth();
        $boxWidth = $this->calculateBoxWidth($maxContentWidth, (int) $options['maxWidth'], $termWidth);

        if (!$this->isTerminalWideEnough($boxWidth, $termWidth) && !$this->fileOutput->isFileOutputRequested()) {
            return $this->generateTerminalTooNarrowMessage($boxWidth, $termWidth);
        }

        return $this->buildCliBox($content, $boxWidth, $options, $colorScheme);
    }

    /**
     * Builds the complete terminal box around prepared content.
     *
     * Responsibility: Builds the complete terminal box around prepared content.
     * @param string[] $content
     * @param array<string, mixed> $options
     * @param array{r: string, c: string} $colorScheme
     */
    private function buildCliBox(array $content, int $boxWidth, array $options, array $colorScheme): string
    {
        $printArea = $boxWidth - 2;
        $highlight = (bool) $options['highlight'];
        $headerLines = (int) $options['headerLines'];
        $footerLines = (int) $options['footerLines'];

        $cliColors = [
            'hf' => ($highlight && $headerLines !== 0) ? "\033{$colorScheme['c']}" : '',
            'reset' => $highlight ? "\033{$colorScheme['r']}" : '',
        ];

        $topLine = $this->boxChars['tl'] . str_repeat($this->boxChars['h'], $printArea) . $this->boxChars['tr'];
        $bottomLine = $this->boxChars['bl'] . str_repeat($this->boxChars['h'], $printArea) . $this->boxChars['br'];
        $separatorLine = $this->boxChars['ls'] . str_repeat($this->boxChars['hs'], $printArea) . $this->boxChars['rs'];

        $lines = [];
        $lines[] = $cliColors['hf'] . $topLine . $cliColors['reset'] . PHP_EOL;
        $this->processContentLines($content, $lines, $cliColors, $printArea, $headerLines, $footerLines, $separatorLine);
        $lines[] = $cliColors['hf'] . $bottomLine . $cliColors['reset'] . PHP_EOL;

        return implode('', $lines);
    }

    /**
     * Appends header, body and footer lines to a terminal box buffer.
     *
     * Responsibility: Appends header, body and footer lines to a terminal box buffer.
     * @param string[] $content
     * @param string[] $lines
     * @param array{hf: string, reset: string} $cliColors
     */
    private function processContentLines(
        array $content,
        array &$lines,
        array $cliColors,
        int $printArea,
        int $headerLines,
        int $footerLines,
        string $separatorLine
    ): void {
        $totalLines = count($content);
        $start = true;

        for ($i = 0; $i < $totalLines; $i++) {
            $line = $content[$i];

            if ($headerLines > 0 && $i < $headerLines) {
                $paddedLine = str_pad($line, $printArea, ' ', STR_PAD_BOTH);
                $lines[] = $cliColors['hf'] . $this->boxChars['v'] . $paddedLine .
                    $this->boxChars['v'] . $cliColors['reset'] . PHP_EOL;

                if ($i === $headerLines - 1) {
                    $lines[] = $cliColors['hf'] . $separatorLine . $cliColors['reset'] . PHP_EOL;
                }
                continue;
            }

            if ($footerLines > 0 && $i >= ($totalLines - $footerLines)) {
                if ($i === $totalLines - $footerLines) {
                    $lines[] = $cliColors['hf'] . $separatorLine . $cliColors['reset'] . PHP_EOL;
                }

                $paddedLine = str_pad($line, $printArea, ' ', STR_PAD_BOTH);
                $lines[] = $cliColors['hf'] . $this->boxChars['v'] . $paddedLine .
                    $this->boxChars['v'] . $cliColors['reset'] . PHP_EOL;
                continue;
            }

            $plainLine = $this->fileOutput->removeAnsiSequences($line);

            if ($start) {
                $lines[] = $cliColors['hf'] . $this->boxChars['v'] . $cliColors['reset'] .
                    str_pad('', $printArea) .
                    $cliColors['hf'] . $this->boxChars['v'] . $cliColors['reset'] . PHP_EOL;
                $start = false;
            }

            if ($printArea >= mb_strlen($plainLine) && $plainLine !== '') {
                $expoLine = explode($plainLine, str_pad($plainLine, $printArea));
                $formattedLine = implode($line, $expoLine);
                $lines[] = $cliColors['hf'] . $this->boxChars['v'] . $cliColors['reset'] .
                    $formattedLine . $cliColors['hf'] . $this->boxChars['v'] .
                    $cliColors['reset'] . PHP_EOL;
            } else {
                $chunks = $this->textHelper->splitLineToFit($line, $printArea - 2);

                foreach ($chunks as $chunkLine) {
                    $chunkLine = str_pad($chunkLine, $printArea - 2);
                    $lines[] = $cliColors['hf'] . $this->boxChars['v'] . $cliColors['reset'] .
                        ' ' . $chunkLine . ' ' . $cliColors['hf'] . $this->boxChars['v'] .
                        $cliColors['reset'] . PHP_EOL;
                }
            }

            if ($i === $totalLines - 1 || ($footerLines > 0 && $i === $totalLines - $footerLines - 1)) {
                $lines[] = $cliColors['hf'] . $this->boxChars['v'] . $cliColors['reset'] .
                    str_pad('', $printArea) .
                    $cliColors['hf'] . $this->boxChars['v'] . $cliColors['reset'] . PHP_EOL;
            }
        }
    }

    /**
     * Returns the widest visible content line.
     *
     * Responsibility: Returns the widest visible content line.
     * @param string[] $content
     */
    private function getMaxContentWidth(array $content): int
    {
        if ($content === []) {
            return 0;
        }

        return max(array_map($this->textHelper->visibleLength(...), $content));
    }

    /**
     * Selects the requested terminal box width.
     *
     * Responsibility: Selects the requested terminal box width.
     */
    private function calculateBoxWidth(int $contentWidth, int $maxWidth, int $termWidth): int
    {
        if ($maxWidth === 0) {
            return $termWidth;
        }

        return $contentWidth > $maxWidth ? $contentWidth : $maxWidth;
    }

    /**
     * Determines whether the terminal can display the selected box width.
     *
     * Responsibility: Determines whether the terminal can display the selected box width.
     */
    private function isTerminalWideEnough(int $boxWidth, int $termWidth): bool
    {
        return $boxWidth <= $termWidth;
    }

    /**
     * Renders the fallback message for terminals that cannot fit the box.
     *
     * Responsibility: Renders the fallback message for terminals that cannot fit the box.
     */
    private function generateTerminalTooNarrowMessage(int $required, int $actual): string
    {
        $message = '!!!Your Terminal Windows is too Narrow. Resize It!!!' . PHP_EOL .
            '==> Minimum Expected: ' . $required . PHP_EOL .
            '==> Given Size:       ' . $actual . PHP_EOL . PHP_EOL .
            'If you cannot Resize the window;' . PHP_EOL .
            'You can Output the data to a file and avoid this error:' . PHP_EOL .
            'php script.php -f="filename"';

        return $this->render(
            preg_split('/\r\n|\r|\n/', rtrim($message)) ?: [],
            [
                'headerLines' => 1,
                'footerLines' => 1,
                'highlight' => true,
                'style' => 2,
                'isError' => true,
                'maxWidth' => 0,
                'enableFileOutput' => false,
            ]
        );
    }

    /**
     * Returns the configured terminal width or its default.
     *
     * Responsibility: Returns the configured terminal width or its default.
     */
    private function getTerminalWidth(): int
    {
        return defined('TW') ? TW : 80;
    }
}
