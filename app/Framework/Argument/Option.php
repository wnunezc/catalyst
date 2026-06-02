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
 * Represents a parsed or predefined command-line option.
 *
 * @package Catalyst\Framework\Argument
 * Responsibility: Stores option names, value/default state, required metadata, description, and value acceptance rules.
 */
class Option
{
    /**
     * Stores the single-character short option name.
     */
    private ?string $shortName = null;

    /**
     * Stores the long option name.
     */
    private ?string $longName = null;

    /**
     * Stores the current parsed or default option value.
     */
    private mixed $value = null;

    /**
     * Indicates whether validation requires this option to be present.
     */
    private bool $required = false;

    /**
     * Stores the fallback value used before the option is parsed.
     */
    private mixed $default = null;

    /**
     * Stores the human-readable option description.
     */
    private string $description = '';

    /**
     * Indicates whether the option requires or accepts an explicit value.
     */
    private bool $acceptsValue = true;

    /**
     * Creates an option definition or parsed option with its default value applied.
     *
     * Responsibility: Creates an option definition or parsed option with its default value applied.
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
     * Returns the configured short option name.
     *
     * Responsibility: Returns the configured short option name.
     * @return string|null
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * Returns the configured long option name.
     *
     * Responsibility: Returns the configured long option name.
     * @return string|null
     */
    public function getLongName(): ?string
    {
        return $this->longName;
    }

    /**
     * Updates the current parsed value for the option.
     *
     * Responsibility: Updates the current parsed value for the option.
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Returns the current parsed or default value.
     *
     * Responsibility: Returns the current parsed or default value.
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Reports whether validation requires this option.
     *
     * Responsibility: Reports whether validation requires this option.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Returns the fallback value assigned to the option.
     *
     * Responsibility: Returns the fallback value assigned to the option.
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Returns the human-readable option description.
     *
     * Responsibility: Returns the human-readable option description.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Reports whether this option accepts an explicit value.
     *
     * Responsibility: Reports whether this option accepts an explicit value.
     * @return bool
     */
    public function acceptsValue(): bool
    {
        return $this->acceptsValue;
    }

    /**
     * Reports whether parsing changed the option value away from its default.
     *
     * @return bool
     */
    public function isSet(): bool
    {
        return $this->value !== $this->default;
    }

    /**
     * Checks whether a supplied name matches the short or long option name.
     *
     * Responsibility: Checks whether a supplied name matches the short or long option name.
     * @param string $name Name to match
     * @return bool
     */
    public function matches(string $name): bool
    {
        // Accept caller input with or without CLI dash prefixes.
        $name = ltrim($name, '-');

        return $name === $this->shortName || $name === $this->longName;
    }

    /**
     * Returns the long option name when available, otherwise the short name.
     *
     * Responsibility: Returns the long option name when available, otherwise the short name.
     * @return string|null
     */
    public function getPrimaryName(): ?string
    {
        return $this->longName ?? $this->shortName;
    }
}
