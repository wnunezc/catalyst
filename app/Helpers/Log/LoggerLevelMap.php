<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Log;

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

    public static function normalize(string $level): ?string
    {
        $normalizedLevel = strtoupper($level);

        return array_key_exists($normalizedLevel, self::PRIORITIES) ? $normalizedLevel : null;
    }

    public static function priority(string $level): ?int
    {
        $normalizedLevel = self::normalize($level);

        return $normalizedLevel !== null ? self::PRIORITIES[$normalizedLevel] : null;
    }

    public static function categoryFor(string $level): string
    {
        return match (true) {
            in_array($level, ['WARNING', 'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR'], true) => 'errors',
            $level === 'NOTICE' => 'events',
            default => 'info',
        };
    }
}
