<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
 *
 */

namespace Catalyst\Framework\Argument;

/**
 * Validator for CLI arguments
 *
 * Validates options and parameters against requirements
 *
 * @package Catalyst\Framework\Argument
 */
class Validator
{
    /**
     * Validation errors
     */
    private array $errors = [];

    /**
     * Validate an option
     *
     * @param Option $option Option to validate
     * @return bool True if valid
     */
    public function validateOption(Option $option): bool
    {
        $this->errors = [];

        // Check if required option is set
        if ($option->isRequired() && !$option->isSet()) {
            $name = $option->getPrimaryName();
            $this->errors[] = "Required option '{$name}' is missing";
            return false;
        }

        // Check if option that accepts value has a valid value
        if ($option->acceptsValue() && $option->isSet()) {
            $value = $option->getValue();
            if ($value === null || $value === '') {
                $name = $option->getPrimaryName();
                $this->errors[] = "Option '{$name}' requires a value";
                return false;
            }
        }

        return true;
    }

    /**
     * Validate a parameter
     *
     * @param Parameter $parameter Parameter to validate
     * @return bool True if valid
     */
    public function validateParameter(Parameter $parameter): bool
    {
        $this->errors = [];

        // Check if required parameter is set
        if ($parameter->isRequired() && !$parameter->hasValue()) {
            $name = $parameter->getName() ?: "Parameter at position {$parameter->getPosition()}";
            $this->errors[] = "Required parameter '{$name}' is missing";
            return false;
        }

        return true;
    }

    /**
     * Validate all options in an ArgumentBag
     *
     * @param ArgumentBag $bag Argument bag to validate
     * @param array<Option> $requiredOptions Array of required options
     * @return bool True if all valid
     */
    public function validateBag(ArgumentBag $bag, array $requiredOptions = []): bool
    {
        $this->errors = [];
        $valid = true;

        // Validate required options
        foreach ($requiredOptions as $requiredOption) {
            $name = $requiredOption->getPrimaryName();
            if (!$bag->hasOption($name)) {
                $this->errors[] = "Required option '{$name}' is missing";
                $valid = false;
            } else {
                $option = $bag->getOption($name);
                if (!$this->validateOption($option)) {
                    $valid = false;
                }
            }
        }

        // Validate all parameters
        foreach ($bag->getAllParameters() as $parameter) {
            if (!$this->validateParameter($parameter)) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if there are validation errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Get validation errors as a formatted string
     *
     * @param string $separator Line separator
     * @return string
     */
    public function getErrorsAsString(string $separator = "\n"): string
    {
        return implode($separator, $this->errors);
    }

    /**
     * Clear validation errors
     *
     * @return void
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Validate option value type
     *
     * @param mixed $value Value to validate
     * @param string $expectedType Expected type (string, int, float, bool, array)
     * @return bool
     */
    public function validateType(mixed $value, string $expectedType): bool
    {
        return match ($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value) || (is_string($value) && ctype_digit($value)),
            'float', 'double' => is_float($value) || is_numeric($value),
            'bool', 'boolean' => is_bool($value) || in_array(strtolower((string)$value), ['true', 'false', '1', '0'], true),
            'array' => is_array($value),
            default => true
        };
    }

    /**
     * Cast value to specified type
     *
     * @param mixed $value Value to cast
     * @param string $type Target type
     * @return mixed Casted value
     */
    public function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'string' => (string)$value,
            'int', 'integer' => (int)$value,
            'float', 'double' => (float)$value,
            'bool', 'boolean' => in_array(strtolower((string)$value), ['true', '1'], true),
            'array' => is_string($value) ? explode(',', $value) : (array)$value,
            default => $value
        };
    }
}
