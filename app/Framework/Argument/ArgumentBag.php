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
 * Stores parsed CLI options, positional parameters, and the original argv array.
 *
 * @package Catalyst\Framework\Argument
 * Responsibility: Provides lookup, existence checks, counts, and array conversion for parsed command-line input.
 */
class ArgumentBag
{
    /**
     * Stores parsed options indexed by their primary name.
     *
     * @var array<string, Option>
     */
    private array $options = [];

    /**
     * Stores parsed positional parameters indexed by numeric position.
     *
     * @var array<int, Parameter>
     */
    private array $parameters = [];

    /**
     * Stores the original argv array for consumers that need unparsed input.
     */
    private array $raw = [];

    /**
     * Captures the original argv array before options and parameters are added.
     *
     * Responsibility: Captures the original argv array before options and parameters are added.
     * @param array $raw Raw argv array
     */
    public function __construct(array $raw = [])
    {
        $this->raw = $raw;
    }

    /**
     * Adds an option to the bag using its primary short or long name.
     *
     * Responsibility: Adds an option to the bag using its primary short or long name.
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
     * Adds a positional parameter to the bag by its declared position.
     *
     * Responsibility: Adds a positional parameter to the bag by its declared position.
     * @param Parameter $parameter
     * @return self
     */
    public function addParameter(Parameter $parameter): self
    {
        $this->parameters[$parameter->getPosition()] = $parameter;
        return $this;
    }

    /**
     * Finds a parsed option by direct key, short name, or long name.
     *
     * Responsibility: Finds a parsed option by direct key, short name, or long name.
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
     * Returns the positional parameter stored at the requested index.
     *
     * Responsibility: Returns the positional parameter stored at the requested index.
     * @param int $position Position index
     * @return Parameter|null
     */
    public function getParameter(int $position): ?Parameter
    {
        return $this->parameters[$position] ?? null;
    }

    /**
     * Checks whether a parsed option exists by short or long name.
     *
     * Responsibility: Checks whether a parsed option exists by short or long name.
     * @param string $name Option name
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return $this->getOption($name) !== null;
    }

    /**
     * Checks whether a positional parameter exists at the requested index.
     *
     * Responsibility: Checks whether a positional parameter exists at the requested index.
     * @param int $position Position index
     * @return bool
     */
    public function hasParameter(int $position): bool
    {
        return isset($this->parameters[$position]);
    }

    /**
     * Returns a parsed option value or the supplied default when absent.
     *
     * Responsibility: Returns a parsed option value or the supplied default when absent.
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
     * Returns a positional parameter value or the supplied default when absent.
     *
     * Responsibility: Returns a positional parameter value or the supplied default when absent.
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
     * Returns all parsed options indexed by primary name.
     *
     * Responsibility: Returns all parsed options indexed by primary name.
     * @return array<string, Option>
     */
    public function getAllOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns all parsed positional parameters indexed by position.
     *
     * Responsibility: Returns all parsed positional parameters indexed by position.
     * @return array<int, Parameter>
     */
    public function getAllParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Converts parsed options and parameters into the flat array format used by legacy CLI consumers.
     *
     * Responsibility: Converts parsed options and parameters into the flat array format used by legacy CLI consumers.
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        // Expose options by both short and long names when available.
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

        // Preserve positional parameters under their numeric indexes.
        foreach ($this->parameters as $position => $parameter) {
            $result[$position] = $parameter->getValue();
        }

        return $result;
    }

    /**
     * Returns the original argv array captured by the bag.
     *
     * Responsibility: Returns the original argv array captured by the bag.
     * @return array
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * Counts parsed options currently stored in the bag.
     *
     * Responsibility: Counts parsed options currently stored in the bag.
     * @return int
     */
    public function countOptions(): int
    {
        return count($this->options);
    }

    /**
     * Counts parsed positional parameters currently stored in the bag.
     *
     * Responsibility: Counts parsed positional parameters currently stored in the bag.
     * @return int
     */
    public function countParameters(): int
    {
        return count($this->parameters);
    }
}
