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
 * Validates parsed CLI options and positional parameters.
 *
 * @package Catalyst\Framework\Argument
 * Responsibility: Tracks validation errors, checks required inputs, validates scalar types, and casts option values.
 */
class Validator
{
    /**
     * Stores validation messages collected during the latest validation pass.
     */
    private array $errors = [];

    /**
     * Validates required and value-bearing constraints for a single option.
     *
     * Responsibility: Validates required and value-bearing constraints for a single option.
     * @param Option $option Option to validate
     * @return bool True if valid
     */
    public function validateOption(Option $option): bool
    {
        $this->errors = [];

        // Required options must differ from their default value.
        if ($option->isRequired() && !$option->isSet()) {
            $name = $option->getPrimaryName();
            $this->errors[] = "Required option '{$name}' is missing";
            return false;
        }

        // Value-bearing options cannot be set to an empty value.
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
     * Validates required value presence for a positional parameter.
     *
     * Responsibility: Validates required value presence for a positional parameter.
     * @param Parameter $parameter Parameter to validate
     * @return bool True if valid
     */
    public function validateParameter(Parameter $parameter): bool
    {
        $this->errors = [];

        // Required parameters must contain a parsed or default value.
        if ($parameter->isRequired() && !$parameter->hasValue()) {
            $name = $parameter->getName() ?: "Parameter at position {$parameter->getPosition()}";
            $this->errors[] = "Required parameter '{$name}' is missing";
            return false;
        }

        return true;
    }

    /**
     * Validates required option definitions and all parameters stored in an argument bag.
     *
     * Responsibility: Validates required option definitions and all parameters stored in an argument bag.
     * @param ArgumentBag $bag Argument bag to validate
     * @param array<Option> $requiredOptions Array of required options
     * @return bool True if all valid
     */
    public function validateBag(ArgumentBag $bag, array $requiredOptions = []): bool
    {
        $this->errors = [];
        $valid = true;

        // Required schema options must be present in the parsed bag.
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

        // Parameters validate their required value metadata individually.
        foreach ($bag->getAllParameters() as $parameter) {
            if (!$this->validateParameter($parameter)) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Returns validation error messages collected by the latest validation pass.
     *
     * Responsibility: Returns validation error messages collected by the latest validation pass.
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Reports whether any validation error messages are currently stored.
     *
     * Responsibility: Reports whether any validation error messages are currently stored.
     * @return bool
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Joins validation error messages using the requested separator.
     *
     * Responsibility: Joins validation error messages using the requested separator.
     * @param string $separator Line separator
     * @return string
     */
    public function getErrorsAsString(string $separator = "\n"): string
    {
        return implode($separator, $this->errors);
    }

    /**
     * Clears all stored validation error messages.
     *
     * Responsibility: Clears all stored validation error messages.
     * @return void
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Checks whether a value is compatible with a supported scalar or array type name.
     *
     * Responsibility: Checks whether a value is compatible with a supported scalar or array type name.
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
     * Casts a value into a supported scalar or array type for downstream CLI consumers.
     *
     * Responsibility: Casts a value into a supported scalar or array type for downstream CLI consumers.
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
