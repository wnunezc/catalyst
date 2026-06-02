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
 * Represents a positional command-line parameter.
 *
 * @package Catalyst\Framework\Argument
 * Responsibility: Stores parameter position, current/default value, required metadata, name, and description.
 */
class Parameter
{
    /**
     * Stores the numeric position assigned during parsing.
     */
    private int $position;

    /**
     * Stores the current parsed or default parameter value.
     */
    private mixed $value = null;

    /**
     * Indicates whether validation requires this parameter to have a value.
     */
    private bool $required = false;

    /**
     * Stores the fallback value used when no explicit value is provided.
     */
    private mixed $default = null;

    /**
     * Stores the parameter name used in validation messages and help text.
     */
    private string $name = '';

    /**
     * Stores the human-readable parameter description.
     */
    private string $description = '';

    /**
     * Creates a positional parameter with parsed value, validation metadata, and fallback value.
     *
     * Responsibility: Creates a positional parameter with parsed value, validation metadata, and fallback value.
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
     * Returns the positional index assigned to this parameter.
     *
     * Responsibility: Returns the positional index assigned to this parameter.
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Updates the current value stored for this parameter.
     *
     * Responsibility: Updates the current value stored for this parameter.
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Returns the current parsed or default parameter value.
     *
     * Responsibility: Returns the current parsed or default parameter value.
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Reports whether validation requires this parameter.
     *
     * Responsibility: Reports whether validation requires this parameter.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Returns the fallback value assigned to this parameter.
     *
     * Responsibility: Returns the fallback value assigned to this parameter.
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Returns the parameter name used for identification and validation messages.
     *
     * Responsibility: Returns the parameter name used for identification and validation messages.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the human-readable parameter description.
     *
     * Responsibility: Returns the human-readable parameter description.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Reports whether the parameter carries a non-default value.
     *
     * @return bool
     */
    public function isSet(): bool
    {
        return $this->value !== null && $this->value !== $this->default;
    }

    /**
     * Reports whether the parameter currently stores any value.
     *
     * Responsibility: Reports whether the parameter currently stores any value.
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->value !== null;
    }
}
