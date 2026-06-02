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
 * Base class for CLI commands
 *
 * Provides colored output helpers and interactive input methods.
 * Concrete commands extend this class and implement execute().
 *
 * @package Catalyst\Framework\Cli
 */
abstract class AbstractCommand implements CommandInterface
{
    /**
     * Default empty options — override in concrete command if needed
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [];
    }

    /**
     * Default empty parameters — override in concrete command if needed
     *
     * @return array
     */
    public function getParameters(): array
    {
        return [];
    }

    // -------------------------------------------------------------------------
    // Output helpers
    // -------------------------------------------------------------------------

    /**
     * Plain output line
     */
    protected function line(string $text): void
    {
        echo $text . PHP_EOL;
    }

    /**
     * Green success line
     */
    protected function success(string $text): void
    {
        echo TerminalStyle::green($text) . PHP_EOL;
    }

    /**
     * Red error line
     */
    protected function error(string $text): void
    {
        echo TerminalStyle::red($text) . PHP_EOL;
    }

    /**
     * Cyan info line
     */
    protected function info(string $text): void
    {
        echo TerminalStyle::cyan($text) . PHP_EOL;
    }

    /**
     * Yellow warning line
     */
    protected function warn(string $text): void
    {
        echo TerminalStyle::yellow($text) . PHP_EOL;
    }

    // -------------------------------------------------------------------------
    // Interactive input helpers
    // -------------------------------------------------------------------------

    /**
     * Prompt user for text input
     *
     * @param string      $question Question to display
     * @param string|null $default  Default value shown in brackets
     * @return string User input (or default if empty input)
     */
    protected function ask(string $question, ?string $default = null): string
    {
        $prompt = $question;
        if ($default !== null) {
            $prompt .= " [{$default}]";
        }
        echo $prompt . ': ';

        $input = trim((string) fgets(STDIN));

        return ($input === '' && $default !== null) ? $default : $input;
    }

    /**
     * Prompt user for yes/no confirmation
     *
     * @param string $question Question to display
     * @param bool   $default  Default answer (true = yes)
     * @return bool
     */
    protected function confirm(string $question, bool $default = false): bool
    {
        $hint = $default ? '[Y/n]' : '[y/N]';
        echo $question . ' ' . $hint . ': ';

        $input = strtolower(trim((string) fgets(STDIN)));

        if ($input === '') {
            return $default;
        }

        return in_array($input, ['y', 'yes'], true);
    }
}
