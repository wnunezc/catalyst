<?php

declare(strict_types=1);

namespace Catalyst\Helpers\ToolBox;

final class DrawBoxFileOutputDecorator
{
    /**
     * @param array<string, string> $boxChars
     */
    public function __construct(private readonly array $boxChars)
    {
    }

    /**
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
