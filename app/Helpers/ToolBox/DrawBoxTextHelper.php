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
 * Measures and splits decorated text for box rendering.
 *
 * @package Catalyst\Helpers\ToolBox
 * Responsibility: Preserves ANSI decoration while fitting visible text into constrained widths.
 */
final class DrawBoxTextHelper
{
    /**
     * Initializes the Draw Box Text Helper instance.
     *
     * Responsibility: Initializes the Draw Box Text Helper instance.
     */
    public function __construct(private readonly FileOutput $fileOutput)
    {
    }

    /**
     * Returns text length excluding ANSI sequences.
     *
     * Responsibility: Returns text length excluding ANSI sequences.
     */
    public function visibleLength(string $string): int
    {
        return mb_strlen($this->fileOutput->removeAnsiSequences($string));
    }

    /**
     * Splits a line while preserving readable key-value alignment.
     *
     * Responsibility: Splits a line while preserving readable key-value alignment.
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
     * Splits text into visible-width chunks while restoring ANSI decoration.
     *
     * Responsibility: Splits text into visible-width chunks while restoring ANSI decoration.
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
     * Extracts the first ANSI style and final reset sequence.
     *
     * Responsibility: Extracts the first ANSI style and final reset sequence.
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
