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

use Catalyst\Helpers\Debug\Dumper;
use Catalyst\Helpers\Debug\ThemeName;
use Catalyst\Helpers\I18n\Translator;

if (!defined('LOADED_DUMP_FUNCTION')) {

    /**
     * Internal handler for variable dumping.
     *
     * Only outputs when IS_DEVELOPMENT is true. Retrieves backtrace to show
     * the exact file and line that called ex() or ex_c().
     *
     * @param array       $var   Variables to dump
     * @param bool        $exit  Whether to exit after dumping
     * @param string|null $theme Optional theme name (defaults to 'monokai')
     */
    function _ex_internal(array $var, bool $exit = false, ?string $theme = null): void
    {
        if (IS_DEVELOPMENT) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $caller    = [
                'file' => $backtrace['file'] ?? 'unknown',
                'line' => $backtrace['line'] ?? 0,
            ];

            Dumper::dump([
                'data'   => $var,
                'caller' => $caller,
                'config' => ['colorTheme' => $theme ?? 'monokai'],
            ]);
        } else {
            echo 'Dump is disabled in production mode.';
        }

        if ($exit) {
            exit;
        }
    }

    /**
     * Dump variables for inspection (development only).
     *
     * Optionally pass a theme name as the last string argument:
     *   ex($var);
     *   ex($var1, $var2, 'dark');
     *
     * @param mixed ...$var Variables to dump, with optional theme name as last argument
     */
    function ex(mixed ...$var): void
    {
        $theme = null;

        if (count($var) > 0 && is_string(end($var)) && ThemeName::exists((string)end($var))) {
            $theme = array_pop($var);
        }

        _ex_internal($var, false, $theme);
    }

    /**
     * Dump variables and immediately exit script execution (development only).
     *
     * @param mixed ...$var Variables to dump, with optional theme name as last argument
     * @return never
     */
    function ex_c(mixed ...$var): never
    {
        $theme = null;

        if (count($var) > 0 && is_string(end($var)) && ThemeName::exists((string)end($var))) {
            $theme = array_pop($var);
        }

        _ex_internal($var, true, $theme);
    }

    /**
     * Escape a value for safe HTML output (XSS prevention).
     *
     * Use in every template echo: <?= e($variable) ?>
     * Never echo user-supplied data without escaping.
     *
     * @param mixed $value       Value to escape (cast to string)
     * @param bool  $doubleEncode Re-encode existing HTML entities (default: true)
     * @return string HTML-safe string
     */
    function e(mixed $value, bool $doubleEncode = true): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }

    /**
     * Get a translated string by dot-notation key (primary i18n helper).
     *
     * Key format: '{group}.{subkey}' — e.g. 'validation.required', 'messages.save_success'
     * Placeholder syntax: :key — e.g. __('validation.min', ['field' => 'Password', 'min' => 8])
     * Falls back to the key itself if no translation is found (never throws).
     *
     * @param string $key          Dot-notation translation key
     * @param array<string, scalar> $replacements Placeholder values
     * @param string|null $locale  Override locale for this call (null = current session/default)
     * @return string Translated string, or $key if not found
     */
    function __(string $key, array $replacements = [], ?string $locale = null): string
    {
        return Translator::getInstance()->get($key, $replacements, $locale);
    }

    /**
     * Alias for __() — get a translated string.
     *
     * @param string $key
     * @param array<string, scalar> $replacements
     * @param string|null $locale
     * @return string
     */
    function t(string $key, array $replacements = [], ?string $locale = null): string
    {
        return Translator::getInstance()->get($key, $replacements, $locale);
    }

    /**
     * Format a date using the current (or specified) locale's format patterns and name translations.
     *
     * Built-in format keys (defined in boot-core/lang/{locale}/dates.json):
     *   'default', 'long', 'short', 'full', 'time', 'datetime', 'iso'
     * You may also pass a literal PHP date() format string (e.g. 'Y-m-d').
     *
     * @param DateTimeInterface|string|int $date   DateTime, date string, or Unix timestamp
     * @param string                        $format Named format key or PHP date() format
     * @param string|null                   $locale Override locale for this call
     * @return string Formatted, localized date string
     */
    function format_date(
        DateTimeInterface|string|int $date,
        string                       $format = 'default',
        ?string                      $locale = null
    ): string {
        return Translator::getInstance()->formatDate($date, $format, $locale);
    }

    /**
     * Read old input prepared by the previous HTML validation redirect.
     *
     * @param string|null $key
     * @param mixed       $default
     * @return mixed
     */
    function old(?string $key = null, mixed $default = null): mixed
    {
        $payload = $GLOBALS['CATALYST_VIEW_OLD_INPUT'] ?? [];

        if (!is_array($payload)) {
            return $key === null ? [] : $default;
        }

        if ($key === null) {
            return $payload;
        }

        return $payload[$key] ?? $default;
    }

    /**
     * @param string|null $key
     * @param string      $bag
     * @return array<string, string[]>|string[]|array<int, string>
     */
    function validation_errors(?string $key = null, string $bag = 'default'): array
    {
        $bags = $GLOBALS['CATALYST_VIEW_VALIDATION_ERRORS'] ?? [];

        if (!is_array($bags)) {
            return [];
        }

        $errors = $bags[$bag] ?? [];
        if (!is_array($errors)) {
            return [];
        }

        if ($key === null) {
            return $errors;
        }

        $messages = $errors[$key] ?? [];

        return is_array($messages) ? $messages : [(string) $messages];
    }

    function validation_error(string $key, string $bag = 'default'): ?string
    {
        $messages = validation_errors($key, $bag);

        return $messages === [] ? null : (string) $messages[0];
    }

    define('LOADED_DUMP_FUNCTION', true);
}
