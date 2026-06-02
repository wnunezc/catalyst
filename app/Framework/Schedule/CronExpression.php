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

namespace Catalyst\Framework\Schedule;

use DateTimeImmutable;

/**
 * Defines the Cron Expression class contract.
 *
 * @package Catalyst\Framework\Schedule
 * Responsibility: Coordinates the cron expression behavior within its module boundary.
 */
final class CronExpression
{
    /**
     * Determines whether is Due.
     */
    public static function isDue(string $expression, DateTimeImmutable $time): bool
    {
        $parts = preg_split('/\s+/', trim($expression)) ?: [];

        if (count($parts) !== 5) {
            return false;
        }

        [$minute, $hour, $day, $month, $weekday] = $parts;

        return self::matchesPart($minute, (int) $time->format('i'), 0, 59)
            && self::matchesPart($hour, (int) $time->format('G'), 0, 23)
            && self::matchesPart($day, (int) $time->format('j'), 1, 31)
            && self::matchesPart($month, (int) $time->format('n'), 1, 12)
            && self::matchesPart($weekday, (int) $time->format('w'), 0, 7, true);
    }

    /**
     * Handles the matches part workflow.
     */
    private static function matchesPart(
        string $expression,
        int $value,
        int $min,
        int $max,
        bool $weekday = false
    ): bool {
        foreach (explode(',', $expression) as $segment) {
            $segment = trim($segment);

            if ($segment === '*') {
                return true;
            }

            if (str_contains($segment, '/')) {
                [$base, $stepRaw] = array_pad(explode('/', $segment, 2), 2, '1');
                $step = max(1, (int) $stepRaw);
                $range = $base === '*' ? [$min, $max] : self::parseRange($base, $min, $max, $weekday);

                if ($range !== null && $value >= $range[0] && $value <= $range[1] && (($value - $range[0]) % $step) === 0) {
                    return true;
                }

                continue;
            }

            $range = self::parseRange($segment, $min, $max, $weekday);

            if ($range !== null && $value >= $range[0] && $value <= $range[1]) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0:int,1:int}|null
     */
    private static function parseRange(string $segment, int $min, int $max, bool $weekday): ?array
    {
        if ($segment === '') {
            return null;
        }

        if (!str_contains($segment, '-')) {
            $normalized = self::normalizeValue((int) $segment, $weekday);

            if ($normalized < $min || $normalized > $max) {
                return null;
            }

            return [$normalized, $normalized];
        }

        [$from, $to] = array_pad(explode('-', $segment, 2), 2, '');
        $start = self::normalizeValue((int) $from, $weekday);
        $end = self::normalizeValue((int) $to, $weekday);

        if ($start > $end || $start < $min || $end > $max) {
            return null;
        }

        return [$start, $end];
    }

    /**
     * Normalizes the provided value.
     */
    private static function normalizeValue(int $value, bool $weekday): int
    {
        if ($weekday && $value === 7) {
            return 0;
        }

        return $value;
    }
}
