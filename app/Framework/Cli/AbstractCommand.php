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
 * Base class for framework CLI commands.
 *
 * Responsibility: Provides shared option defaults, terminal output helpers and interactive prompts for concrete commands.
 *
 * @package Catalyst\Framework\Cli
 */
abstract class AbstractCommand implements CommandInterface
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [];
    }

    /**
     * Defines the accepted positional parameter schema for this command.
     *
     * Responsibility: Defines the accepted positional parameter schema for this command.
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return [];
    }

    // -------------------------------------------------------------------------
    // Output helpers
    // -------------------------------------------------------------------------

    /**
     * Writes a plain terminal line.
     *
     * Responsibility: Writes a plain terminal line.
     */
    protected function line(string $text): void
    {
        echo $text . PHP_EOL;
    }

    /**
     * Writes a success terminal line.
     *
     * Responsibility: Writes a success terminal line.
     */
    protected function success(string $text): void
    {
        echo TerminalStyle::green($text) . PHP_EOL;
    }

    /**
     * Writes an error terminal line.
     *
     * Responsibility: Writes an error terminal line.
     */
    protected function error(string $text): void
    {
        echo TerminalStyle::red($text) . PHP_EOL;
    }

    /**
     * Writes an informational terminal line.
     *
     * Responsibility: Writes an informational terminal line.
     */
    protected function info(string $text): void
    {
        echo TerminalStyle::cyan($text) . PHP_EOL;
    }

    /**
     * Writes a warning terminal line.
     *
     * Responsibility: Writes a warning terminal line.
     */
    protected function warn(string $text): void
    {
        echo TerminalStyle::yellow($text) . PHP_EOL;
    }

    // -------------------------------------------------------------------------
    // Interactive input helpers
    // -------------------------------------------------------------------------

    /**
     * Prompts for text input and applies the default when input is empty.
     *
     * Responsibility: Prompts for text input and applies the default when input is empty.
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
     * Prompts for yes/no input and applies the configured default.
     *
     * Responsibility: Prompts for yes/no input and applies the configured default.
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
