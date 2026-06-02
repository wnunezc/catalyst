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

namespace Catalyst\Helpers\Log;

/**
 * Maps logger levels to priorities and output categories.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Normalizes level names and resolves filtering priorities and directories.
 */
final class LoggerLevelMap
{
    /**
     * @var array<string, int>
     */
    public const PRIORITIES = [
        'EMERGENCY' => 0,
        'ALERT' => 1,
        'CRITICAL' => 2,
        'ERROR' => 3,
        'WARNING' => 4,
        'NOTICE' => 5,
        'INFO' => 6,
        'DEBUG' => 7,
    ];

    /**
     * Returns supported logger channel names.
     *
     * @return string[]
     */
    public static function channels(): array
    {
        return ['single', 'daily', 'stderr'];
    }

    /**
     * Normalizes a logger level name when supported.
     */
    public static function normalize(string $level): ?string
    {
        $normalizedLevel = strtoupper($level);

        return array_key_exists($normalizedLevel, self::PRIORITIES) ? $normalizedLevel : null;
    }

    /**
     * Returns the numeric priority for a logger level.
     */
    public static function priority(string $level): ?int
    {
        $normalizedLevel = self::normalize($level);

        return $normalizedLevel !== null ? self::PRIORITIES[$normalizedLevel] : null;
    }

    /**
     * Returns the output directory category for a logger level.
     */
    public static function categoryFor(string $level): string
    {
        return match (true) {
            in_array($level, ['WARNING', 'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR'], true) => 'errors',
            $level === 'NOTICE' => 'events',
            default => 'info',
        };
    }
}
