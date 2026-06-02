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
 * Defines the Logger Level Map class contract.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Coordinates the logger level map behavior within its module boundary.
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
     * @return string[]
     */
    public static function channels(): array
    {
        return ['single', 'daily', 'stderr'];
    }

    /**
     * Normalizes the provided value.
     */
    public static function normalize(string $level): ?string
    {
        $normalizedLevel = strtoupper($level);

        return array_key_exists($normalizedLevel, self::PRIORITIES) ? $normalizedLevel : null;
    }

    /**
     * Handles the priority workflow.
     */
    public static function priority(string $level): ?int
    {
        $normalizedLevel = self::normalize($level);

        return $normalizedLevel !== null ? self::PRIORITIES[$normalizedLevel] : null;
    }

    /**
     * Handles the category for workflow.
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
