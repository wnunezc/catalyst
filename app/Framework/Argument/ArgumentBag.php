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
 * Container for parsed CLI arguments
 *
 * Stores options and parameters in a structured way
 *
 * @package Catalyst\Framework\Argument
 */
class ArgumentBag
{
    /**
     * Options (flags) indexed by name
     *
     * @var array<string, Option>
     */
    private array $options = [];

    /**
     * Parameters indexed by position
     *
     * @var array<int, Parameter>
     */
    private array $parameters = [];

    /**
     * Raw argv array
     */
    private array $raw = [];

    /**
     * Constructor
     *
     * @param array $raw Raw argv array
     */
    public function __construct(array $raw = [])
    {
        $this->raw = $raw;
    }

    /**
     * Add an option
     *
     * @param Option $option
     * @return self
     */
    public function addOption(Option $option): self
    {
        $name = $option->getPrimaryName();
        if ($name !== null) {
            $this->options[$name] = $option;
        }

        return $this;
    }

    /**
     * Add a parameter
     *
     * @param Parameter $parameter
     * @return self
     */
    public function addParameter(Parameter $parameter): self
    {
        $this->parameters[$parameter->getPosition()] = $parameter;
        return $this;
    }

    /**
     * Get option by name (short or long)
     *
     * @param string $name Option name
     * @return Option|null
     */
    public function getOption(string $name): ?Option
    {
        // Try direct match first
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        // Try to find by short or long name
        foreach ($this->options as $option) {
            if ($option->matches($name)) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Get parameter by position
     *
     * @param int $position Position index
     * @return Parameter|null
     */
    public function getParameter(int $position): ?Parameter
    {
        return $this->parameters[$position] ?? null;
    }

    /**
     * Check if option exists
     *
     * @param string $name Option name
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return $this->getOption($name) !== null;
    }

    /**
     * Check if parameter exists at position
     *
     * @param int $position Position index
     * @return bool
     */
    public function hasParameter(int $position): bool
    {
        return isset($this->parameters[$position]);
    }

    /**
     * Get option value
     *
     * @param string $name Option name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function getOptionValue(string $name, mixed $default = null): mixed
    {
        $option = $this->getOption($name);
        return $option?->getValue() ?? $default;
    }

    /**
     * Get parameter value
     *
     * @param int $position Position index
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function getParameterValue(int $position, mixed $default = null): mixed
    {
        $parameter = $this->getParameter($position);
        return $parameter?->getValue() ?? $default;
    }

    /**
     * Get all options
     *
     * @return array<string, Option>
     */
    public function getAllOptions(): array
    {
        return $this->options;
    }

    /**
     * Get all parameters
     *
     * @return array<int, Parameter>
     */
    public function getAllParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Convert to array format compatible with FileOutput.php
     *
     * Returns associative array with option names as keys
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        // Add all options with their values
        foreach ($this->options as $option) {
            $shortName = $option->getShortName();
            $longName = $option->getLongName();
            $value = $option->getValue();

            if ($shortName !== null) {
                $result[$shortName] = $value;
            }
            if ($longName !== null) {
                $result[$longName] = $value;
            }
        }

        // Add parameters as indexed array
        foreach ($this->parameters as $position => $parameter) {
            $result[$position] = $parameter->getValue();
        }

        return $result;
    }

    /**
     * Get raw argv
     *
     * @return array
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * Count options
     *
     * @return int
     */
    public function countOptions(): int
    {
        return count($this->options);
    }

    /**
     * Count parameters
     *
     * @return int
     */
    public function countParameters(): int
    {
        return count($this->parameters);
    }
}
