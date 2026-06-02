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

namespace Catalyst\Framework\Argument;

/**
 * Parses raw CLI argv input into structured option and parameter objects.
 *
 * @package Catalyst\Framework\Argument
 * Responsibility: Recognizes long options, short options, combined short flags, option values, and positional parameters.
 */
class ArgumentParser
{
    /**
     * Parses an argv array into an argument bag, skipping the executable script name.
     *
     * Responsibility: Parses an argv array into an argument bag, skipping the executable script name.
     * @param array $argv Raw command line arguments
     * @return ArgumentBag
     */
    public function parse(array $argv): ArgumentBag
    {
        $bag = new ArgumentBag($argv);

        // The first argv item is the script name and is not exposed as a parameter.
        $args = array_slice($argv, 1);

        $parameterPosition = 0;
        $i = 0;
        $count = count($args);

        while ($i < $count) {
            $arg = $args[$i];

            // Long options may be boolean flags or carry values inline/after the flag.
            if (str_starts_with($arg, '--')) {
                $i = $this->parseLongOption($args, $i, $bag);
            }
            // Short options may be single flags with values or combined boolean flags.
            elseif (str_starts_with($arg, '-') && strlen($arg) > 1) {
                $i = $this->parseShortOption($args, $i, $bag);
            }
            // Non-option tokens are stored as positional parameters in encounter order.
            else {
                $parameter = new Parameter($parameterPosition++, $arg);
                $bag->addParameter($parameter);
                $i++;
            }
        }

        return $bag;
    }

    /**
     * Parses a long option token and stores it in the provided bag.
     *
     * Responsibility: Parses a long option token and stores it in the provided bag.
     * @param array $args All arguments
     * @param int $index Current index
     * @param ArgumentBag $bag Argument bag to add to
     * @return int Next index to process
     */
    private function parseLongOption(array $args, int $index, ArgumentBag $bag): int
    {
        $arg = $args[$index];
        $name = substr($arg, 2); // Strip the long-option marker.
        $value = true; // Boolean long flags default to true.

        // Inline assignment keeps the value in the same token.
        if (str_contains($name, '=')) {
            [$name, $value] = explode('=', $name, 2);
        }
        // A following non-option token is consumed as the option value.
        elseif (isset($args[$index + 1]) && !str_starts_with($args[$index + 1], '-')) {
            $value = $args[$index + 1];
            $index++; // Advance past the consumed value token.
        }

        $option = new Option(null, $name, null, false, '', $value !== true);
        $option->setValue($value);
        $bag->addOption($option);

        return $index + 1;
    }

    /**
     * Parses a short option token or combined short flags and stores them in the bag.
     *
     * Responsibility: Parses a short option token or combined short flags and stores them in the bag.
     * @param array $args All arguments
     * @param int $index Current index
     * @param ArgumentBag $bag Argument bag to add to
     * @return int Next index to process
     */
    private function parseShortOption(array $args, int $index, ArgumentBag $bag): int
    {
        $arg = $args[$index];
        $flags = substr($arg, 1); // Strip the short-option marker.

        // A single short flag may consume the following value token.
        if (strlen($flags) === 1) {
            $value = true;

            // A following non-option token is consumed as the short-option value.
            if (isset($args[$index + 1]) && !str_starts_with($args[$index + 1], '-')) {
                $value = $args[$index + 1];
                $index++; // Advance past the consumed value token.
            }

            $option = new Option($flags, null, null, false, '', $value !== true);
            $option->setValue($value);
            $bag->addOption($option);

            return $index + 1;
        }

        // Combined short flags are stored as independent boolean options.
        for ($j = 0; $j < strlen($flags); $j++) {
            $flag = $flags[$j];
            $option = new Option($flag, null, null, false, '', false);
            $option->setValue(true);
            $bag->addOption($option);
        }

        return $index + 1;
    }

    /**
     * Parses argv and overlays predefined option objects onto matching parsed options.
     *
     * Responsibility: Parses argv and overlays predefined option objects onto matching parsed options.
     * @param array $argv Raw command line arguments
     * @param array<Option> $definedOptions Array of predefined Option objects
     * @return ArgumentBag
     */
    public function parseWithSchema(array $argv, array $definedOptions): ArgumentBag
    {
        $bag = $this->parse($argv);

        // Match parsed values back onto their schema definitions.
        foreach ($definedOptions as $definedOption) {
            $shortName = $definedOption->getShortName();
            $longName = $definedOption->getLongName();

            // Locate a parsed option by either schema name.
            $parsedOption = null;
            if ($shortName && $bag->hasOption($shortName)) {
                $parsedOption = $bag->getOption($shortName);
            } elseif ($longName && $bag->hasOption($longName)) {
                $parsedOption = $bag->getOption($longName);
            }

            // Preserve the schema object while carrying the parsed value forward.
            if ($parsedOption) {
                $definedOption->setValue($parsedOption->getValue());
            }

            // Store schema definitions in the bag so validation can inspect metadata.
            $bag->addOption($definedOption);
        }

        return $bag;
    }
}
