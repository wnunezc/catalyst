<?php

declare(strict_types=1);

namespace Catalyst\Helpers\ToolBox;

use Catalyst\Helpers\IO\FileOutput;

final class DrawBoxTextHelper
{
    public function __construct(private readonly FileOutput $fileOutput)
    {
    }

    public function visibleLength(string $string): int
    {
        return mb_strlen($this->fileOutput->removeAnsiSequences($string));
    }

    /**
     * @return string[]
     */
    public function splitLineToFit(string $line, int $maxWidth): array
    {
        $plainLine = $this->fileOutput->removeAnsiSequences($line);
        $delimiter = str_contains($plainLine, '=>') ? ' =>' : (str_contains($plainLine, ':') ? ':' : null);

        if ($delimiter === null || mb_strlen($plainLine) <= $maxWidth) {
            return $this->splitTextToChunks($line, $maxWidth);
        }

        $parts = explode($delimiter, $line, 2);
        $plainParts = explode($delimiter, $plainLine, 2);

        $keyPart = $parts[0];
        $valuePart = $parts[1] ?? '';
        $keyLength = mb_strlen($plainParts[0]);
        $delimiterLength = mb_strlen($delimiter);
        $valueWidth = $maxWidth - $keyLength - $delimiterLength;

        if ($valueWidth < 10) {
            return $this->splitTextToChunks($line, $maxWidth);
        }

        $valueChunks = $this->splitTextToChunks($valuePart, $valueWidth);

        $result = [];
        foreach ($valueChunks as $index => $chunk) {
            if ($index === 0) {
                $result[] = $keyPart . $delimiter . $chunk;
                continue;
            }

            $padding = str_repeat(' ', $keyLength + $delimiterLength);
            $result[] = $padding . $chunk;
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function splitTextToChunks(string $text, int $maxWidth): array
    {
        if ($maxWidth < 5) {
            $maxWidth = 5;
        }

        $plainText = $this->fileOutput->removeAnsiSequences($text);
        if (mb_strlen($plainText) <= $maxWidth) {
            return [$text];
        }

        $ansiCodes = $this->extractAnsiCodes($text);
        $startCode = $ansiCodes['start'] ?? '';
        $resetCode = $ansiCodes['reset'] ?? '';

        $chunks = [];
        $textLength = mb_strlen($plainText);
        for ($i = 0; $i < $textLength; $i += $maxWidth) {
            $chunks[] = mb_substr($plainText, $i, $maxWidth);
        }

        if ($startCode && $resetCode) {
            $chunks = array_map(
                static fn(string $chunk): string => $startCode . $chunk . $resetCode,
                $chunks
            );
        }

        return $chunks;
    }

    /**
     * @return array{start: ?string, reset: ?string}
     */
    private function extractAnsiCodes(string $string): array
    {
        $startPattern = '/\033\[(\d+(;\d+)*)m/';
        $resetPattern = '/\033\[0m/';

        preg_match($startPattern, $string, $startMatch);
        preg_match_all($resetPattern, $string, $resetMatches);

        return [
            'start' => $startMatch[0] ?? null,
            'reset' => end($resetMatches[0]) ?: null,
        ];
    }
}
