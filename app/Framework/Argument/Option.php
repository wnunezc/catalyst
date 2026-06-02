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
 * Represents a CLI option (flag)
 *
 * Options can be short (-f) or long (--file) and may have values
 *
 * @package Catalyst\Framework\Argument
 */
class Option
{
    /**
     * Short name of the option (single character)
     */
    private ?string $shortName = null;

    /**
     * Long name of the option
     */
    private ?string $longName = null;

    /**
     * Value of the option
     */
    private mixed $value = null;

    /**
     * Whether this option is required
     */
    private bool $required = false;

    /**
     * Default value if option is not provided
     */
    private mixed $default = null;

    /**
     * Description of the option
     */
    private string $description = '';

    /**
     * Whether this option accepts a value
     */
    private bool $acceptsValue = true;

    /**
     * Constructor
     *
     * @param string|null $shortName Short name (single character)
     * @param string|null $longName Long name
     * @param mixed $default Default value
     * @param bool $required Whether option is required
     * @param string $description Option description
     * @param bool $acceptsValue Whether option accepts a value
     */
    public function __construct(
        ?string $shortName = null,
        ?string $longName = null,
        mixed $default = null,
        bool $required = false,
        string $description = '',
        bool $acceptsValue = true
    ) {
        $this->shortName = $shortName;
        $this->longName = $longName;
        $this->default = $default;
        $this->required = $required;
        $this->description = $description;
        $this->acceptsValue = $acceptsValue;
        $this->value = $default;
    }

    /**
     * Get short name
     *
     * @return string|null
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * Get long name
     *
     * @return string|null
     */
    public function getLongName(): ?string
    {
        return $this->longName;
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
     * Check if option is required
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
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Check if option accepts a value
     *
     * @return bool
     */
    public function acceptsValue(): bool
    {
        return $this->acceptsValue;
    }

    /**
     * Check if option has been set
     *
     * @return bool
     */
    public function isSet(): bool
    {
        return $this->value !== $this->default;
    }

    /**
     * Match option name (short or long)
     *
     * @param string $name Name to match
     * @return bool
     */
    public function matches(string $name): bool
    {
        // Remove leading dashes
        $name = ltrim($name, '-');

        return $name === $this->shortName || $name === $this->longName;
    }

    /**
     * Get primary name (long if available, otherwise short)
     *
     * @return string|null
     */
    public function getPrimaryName(): ?string
    {
        return $this->longName ?? $this->shortName;
    }
}
