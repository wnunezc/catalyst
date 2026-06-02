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
 * Parser for CLI arguments
 *
 * Converts $argv into structured Option and Parameter objects
 *
 * @package Catalyst\Framework\Argument
 */
class ArgumentParser
{
    /**
     * Parse argv array into ArgumentBag
     *
     * @param array $argv Raw command line arguments
     * @return ArgumentBag
     */
    public function parse(array $argv): ArgumentBag
    {
        $bag = new ArgumentBag($argv);

        // Skip script name (first argument)
        $args = array_slice($argv, 1);

        $parameterPosition = 0;
        $i = 0;
        $count = count($args);

        while ($i < $count) {
            $arg = $args[$i];

            // Long option: --option or --option=value
            if (str_starts_with($arg, '--')) {
                $i = $this->parseLongOption($args, $i, $bag);
            }
            // Short option: -o or -o value or combined -abc
            elseif (str_starts_with($arg, '-') && strlen($arg) > 1) {
                $i = $this->parseShortOption($args, $i, $bag);
            }
            // Positional parameter
            else {
                $parameter = new Parameter($parameterPosition++, $arg);
                $bag->addParameter($parameter);
                $i++;
            }
        }

        return $bag;
    }

    /**
     * Parse long option (--option or --option=value)
     *
     * @param array $args All arguments
     * @param int $index Current index
     * @param ArgumentBag $bag Argument bag to add to
     * @return int Next index to process
     */
    private function parseLongOption(array $args, int $index, ArgumentBag $bag): int
    {
        $arg = $args[$index];
        $name = substr($arg, 2); // Remove --
        $value = true; // Default for boolean flags

        // Check for --option=value format
        if (str_contains($name, '=')) {
            [$name, $value] = explode('=', $name, 2);
        }
        // Check if next argument is a value (not another option)
        elseif (isset($args[$index + 1]) && !str_starts_with($args[$index + 1], '-')) {
            $value = $args[$index + 1];
            $index++; // Skip next argument as it's the value
        }

        $option = new Option(null, $name, null, false, '', $value !== true);
        $option->setValue($value);
        $bag->addOption($option);

        return $index + 1;
    }

    /**
     * Parse short option (-o or -o value or combined -abc)
     *
     * @param array $args All arguments
     * @param int $index Current index
     * @param ArgumentBag $bag Argument bag to add to
     * @return int Next index to process
     */
    private function parseShortOption(array $args, int $index, ArgumentBag $bag): int
    {
        $arg = $args[$index];
        $flags = substr($arg, 1); // Remove -

        // Single flag: -f value
        if (strlen($flags) === 1) {
            $value = true;

            // Check if next argument is a value
            if (isset($args[$index + 1]) && !str_starts_with($args[$index + 1], '-')) {
                $value = $args[$index + 1];
                $index++; // Skip next argument
            }

            $option = new Option($flags, null, null, false, '', $value !== true);
            $option->setValue($value);
            $bag->addOption($option);

            return $index + 1;
        }

        // Combined flags: -abc
        // Each flag is treated as boolean
        for ($j = 0; $j < strlen($flags); $j++) {
            $flag = $flags[$j];
            $option = new Option($flag, null, null, false, '', false);
            $option->setValue(true);
            $bag->addOption($option);
        }

        return $index + 1;
    }

    /**
     * Parse arguments with predefined options schema
     *
     * @param array $argv Raw command line arguments
     * @param array<Option> $definedOptions Array of predefined Option objects
     * @return ArgumentBag
     */
    public function parseWithSchema(array $argv, array $definedOptions): ArgumentBag
    {
        $bag = $this->parse($argv);

        // Match parsed options with defined options
        foreach ($definedOptions as $definedOption) {
            $shortName = $definedOption->getShortName();
            $longName = $definedOption->getLongName();

            // Check if this option was provided
            $parsedOption = null;
            if ($shortName && $bag->hasOption($shortName)) {
                $parsedOption = $bag->getOption($shortName);
            } elseif ($longName && $bag->hasOption($longName)) {
                $parsedOption = $bag->getOption($longName);
            }

            // If found, update defined option with parsed value
            if ($parsedOption) {
                $definedOption->setValue($parsedOption->getValue());
            }

            // Add defined option to bag (replaces basic parsed version)
            $bag->addOption($definedOption);
        }

        return $bag;
    }
}
