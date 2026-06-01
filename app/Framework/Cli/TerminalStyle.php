<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli;

final class TerminalStyle
{
    private const RESET  = "\033[0m";
    private const RED    = "\033[31m";
    private const GREEN  = "\033[32m";
    private const YELLOW = "\033[33m";
    private const CYAN   = "\033[36m";

    private static ?bool $supportsAnsi = null;

    public static function red(string $text): string
    {
        return self::wrap($text, self::RED);
    }

    public static function green(string $text): string
    {
        return self::wrap($text, self::GREEN);
    }

    public static function yellow(string $text): string
    {
        return self::wrap($text, self::YELLOW);
    }

    public static function cyan(string $text): string
    {
        return self::wrap($text, self::CYAN);
    }

    public static function supportsAnsi(): bool
    {
        if (self::$supportsAnsi !== null) {
            return self::$supportsAnsi;
        }

        if (getenv('NO_COLOR') !== false || getenv('TERM') === 'dumb') {
            self::$supportsAnsi = false;
            return self::$supportsAnsi;
        }

        if (defined('STDOUT') && function_exists('stream_isatty')) {
            self::$supportsAnsi = @stream_isatty(STDOUT);
            return self::$supportsAnsi;
        }

        self::$supportsAnsi = DIRECTORY_SEPARATOR !== '\\';

        return self::$supportsAnsi;
    }

    private static function wrap(string $text, string $color): string
    {
        if (!self::supportsAnsi()) {
            return $text;
        }

        return $color . $text . self::RESET;
    }
}
