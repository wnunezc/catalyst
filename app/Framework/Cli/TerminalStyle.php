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

namespace Catalyst\Framework\Cli;

/**
 * Terminal color formatter.
 *
 * Responsibility: Wraps CLI output text with ANSI color sequences when supported.
 *
 * @package Catalyst\Framework\Cli
 */
final class TerminalStyle
{
    private const RESET  = "\033[0m";
    private const RED    = "\033[31m";
    private const GREEN  = "\033[32m";
    private const YELLOW = "\033[33m";
    private const CYAN   = "\033[36m";

    private static ?bool $supportsAnsi = null;

    /**
     * Wraps text in the red ANSI color when supported.
     */
    public static function red(string $text): string
    {
        return self::wrap($text, self::RED);
    }

    /**
     * Wraps text in the green ANSI color when supported.
     */
    public static function green(string $text): string
    {
        return self::wrap($text, self::GREEN);
    }

    /**
     * Wraps text in the yellow ANSI color when supported.
     */
    public static function yellow(string $text): string
    {
        return self::wrap($text, self::YELLOW);
    }

    /**
     * Wraps text in the cyan ANSI color when supported.
     */
    public static function cyan(string $text): string
    {
        return self::wrap($text, self::CYAN);
    }

    /**
     * Determines whether the current output stream supports ANSI color.
     */
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

    /**
     * Applies an ANSI color wrapper when supported.
     */
    private static function wrap(string $text, string $color): string
    {
        if (!self::supportsAnsi()) {
            return $text;
        }

        return $color . $text . self::RESET;
    }
}
