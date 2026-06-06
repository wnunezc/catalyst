<?php

declare(strict_types=1);

namespace CatalystTest\Support;

use RuntimeException;

final class Assert
{
    public static function true(bool $condition, string $message = 'Expected condition to be true.'): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    public static function false(bool $condition, string $message = 'Expected condition to be false.'): void
    {
        if ($condition) {
            throw new RuntimeException($message);
        }
    }

    public static function same(mixed $expected, mixed $actual, string $message = 'Values are not identical.'): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . '.');
        }
    }

    public static function notSame(mixed $unexpected, mixed $actual, string $message = 'Values should not be identical.'): void
    {
        if ($unexpected === $actual) {
            throw new RuntimeException($message . ' Unexpected value ' . var_export($actual, true) . '.');
        }
    }

    public static function contains(string $needle, string $haystack, string $message = 'String does not contain expected fragment.'): void
    {
        if (!str_contains($haystack, $needle)) {
            throw new RuntimeException($message . ' Missing ' . var_export($needle, true) . '.');
        }
    }
}
