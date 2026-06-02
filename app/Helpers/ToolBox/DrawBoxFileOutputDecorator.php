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

/**
 * Appends file-output status to a rendered CLI box.
 *
 * @package Catalyst\Helpers\ToolBox
 * Responsibility: Inserts a colored separator and centered persistence message before the box footer.
 */
final class DrawBoxFileOutputDecorator
{
    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param array<string, string> $boxChars
     */
    public function __construct(private readonly array $boxChars)
    {
    }

    /**
     * Appends a file-output result line to an existing CLI box.
     *
     * Responsibility: Appends a file-output result line to an existing CLI box.
     * @param array{success: bool, filename: string, message: string} $result
     */
    public function append(string $boxOutput, array $result): string
    {
        $lines = explode(PHP_EOL, $boxOutput);
        $bottomLine = array_pop($lines);
        $printArea = mb_strlen($lines[0] ?? '') - 2;
        $separatorLine = $this->boxChars['ls'] . str_repeat($this->boxChars['hs'], $printArea) . $this->boxChars['rs'];
        $statusLine = str_pad($result['message'], $printArea, ' ', STR_PAD_BOTH);

        $colorCode = '';
        $resetCode = '';
        if (preg_match('/(\x1B\[\d+(?:;\d+)*m)/', $boxOutput, $colorMatches)) {
            $colorCode = $colorMatches[1] ?? '';
            if (preg_match('/(\x1B\[0m)/', $boxOutput, $resetMatches)) {
                $resetCode = $resetMatches[1] ?? '';
            }
        }

        $newLines = implode(PHP_EOL, $lines);
        $fileStatusInfo = PHP_EOL . $colorCode . $separatorLine . $resetCode . PHP_EOL .
            $colorCode . $this->boxChars['v'] . $resetCode . $statusLine .
            $colorCode . $this->boxChars['v'] . $resetCode . PHP_EOL;

        return $newLines . $fileStatusInfo . $bottomLine;
    }
}
