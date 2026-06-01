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
 * Represents a positional CLI parameter
 *
 * Parameters are non-flag arguments (e.g., file paths, commands)
 *
 * @package Catalyst\Framework\Argument
 */
class Parameter
{
    /**
     * Position index of the parameter
     */
    private int $position;

    /**
     * Value of the parameter
     */
    private mixed $value = null;

    /**
     * Whether this parameter is required
     */
    private bool $required = false;

    /**
     * Default value if parameter is not provided
     */
    private mixed $default = null;

    /**
     * Name/description of the parameter
     */
    private string $name = '';

    /**
     * Description of the parameter
     */
    private string $description = '';

    /**
     * Constructor
     *
     * @param int $position Position index
     * @param mixed $value Value of the parameter
     * @param bool $required Whether parameter is required
     * @param mixed $default Default value
     * @param string $name Name/identifier
     * @param string $description Description
     */
    public function __construct(
        int $position,
        mixed $value = null,
        bool $required = false,
        mixed $default = null,
        string $name = '',
        string $description = ''
    ) {
        $this->position = $position;
        $this->value = $value ?? $default;
        $this->required = $required;
        $this->default = $default;
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Set value
     *
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Check if parameter is required
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get default value
     *
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Check if parameter has been set
     *
     * @return bool
     */
    public function isSet(): bool
    {
        return $this->value !== null && $this->value !== $this->default;
    }

    /**
     * Check if parameter has a value
     *
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->value !== null;
    }
}
