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

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Provides the shared entry point for parsing and querying CLI arguments.
 *
 * @package Catalyst\Framework\Argument
 * Responsibility: Maintains parser, validator, parsed bag, and optional option schema for command-line consumers.
 */
class Argument
{
    use SingletonTrait;

    /**
     * Holds the latest parsed command-line arguments.
     */
    private ?ArgumentBag $bag = null;

    /**
     * Converts raw argv input into an argument bag.
     */
    private ArgumentParser $parser;

    /**
     * Validates parsed arguments against defined option requirements.
     */
    private Validator $validator;

    /**
     * Stores option definitions used when parsing with a schema.
     *
     * @var array<Option>
     */
    private array $definedOptions = [];

    /**
     * Initializes parsing and validation services, then parses the current CLI input.
     *
     * Responsibility: Initializes parsing and validation services, then parses the current CLI input.
     */
    protected function __construct()
    {
        $this->parser = new ArgumentParser();
        $this->validator = new Validator();

        // Auto-parse on instantiation
        $this->parse();
    }

    /**
     * Parses raw command-line input into the internal argument bag.
     *
     * Responsibility: Parses raw command-line input into the internal argument bag.
     * @param array|null $argv Override argv (useful for testing)
     * @return self
     */
    public function parse(?array $argv = null): self
    {
        // Use global $argv if not provided
        $argv = $argv ?? $_SERVER['argv'] ?? [];

        // Parse with schema if defined options exist
        if (!empty($this->definedOptions)) {
            $this->bag = $this->parser->parseWithSchema($argv, $this->definedOptions);
        } else {
            $this->bag = $this->parser->parse($argv);
        }

        return $this;
    }

    /**
     * Returns parsed options and positional parameters as a flat associative array.
     *
     * Responsibility: Returns parsed options and positional parameters as a flat associative array.
     * @return array
     */
    public function getArguments(): array
    {
        if ($this->bag === null) {
            $this->parse();
        }

        return $this->bag->toArray();
    }

    /**
     * Checks whether a parsed option is present by short or long name.
     *
     * Responsibility: Checks whether a parsed option is present by short or long name.
     * @param string $name Option name (short or long)
     * @return bool
     */
    public function has(string $name): bool
    {
        if ($this->bag === null) {
            $this->parse();
        }

        return $this->bag->hasOption($name);
    }

    /**
     * Returns a parsed option value or the supplied default when the option is absent.
     *
     * Responsibility: Returns a parsed option value or the supplied default when the option is absent.
     * @param string $name Option name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        if ($this->bag === null) {
            $this->parse();
        }

        return $this->bag->getOptionValue($name, $default);
    }

    /**
     * Returns a positional parameter value or the supplied default when absent.
     *
     * Responsibility: Returns a positional parameter value or the supplied default when absent.
     * @param int $position Position index
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function getParameter(int $position, mixed $default = null): mixed
    {
        if ($this->bag === null) {
            $this->parse();
        }

        return $this->bag->getParameterValue($position, $default);
    }

    /**
     * Returns every parsed option object indexed by primary name.
     *
     * Responsibility: Returns every parsed option object indexed by primary name.
     * @return array<string, Option>
     */
    public function getAllOptions(): array
    {
        if ($this->bag === null) {
            $this->parse();
        }

        return $this->bag->getAllOptions();
    }

    /**
     * Returns every parsed positional parameter indexed by position.
     *
     * Responsibility: Returns every parsed positional parameter indexed by position.
     * @return array<int, Parameter>
     */
    public function getAllParameters(): array
    {
        if ($this->bag === null) {
            $this->parse();
        }

        return $this->bag->getAllParameters();
    }

    /**
     * Registers a single option definition for schema-aware parsing and validation.
     *
     * Responsibility: Registers a single option definition for schema-aware parsing and validation.
     * @param Option $option Option definition
     * @return self
     */
    public function defineOption(Option $option): self
    {
        $this->definedOptions[] = $option;
        return $this;
    }

    /**
     * Registers multiple option definitions for schema-aware parsing and validation.
     *
     * Responsibility: Registers multiple option definitions for schema-aware parsing and validation.
     * @param array<Option> $options Array of Option objects
     * @return self
     */
    public function defineOptions(array $options): self
    {
        foreach ($options as $option) {
            $this->defineOption($option);
        }
        return $this;
    }

    /**
     * Validates the current parsed bag against all required defined options.
     *
     * Responsibility: Validates the current parsed bag against all required defined options.
     * @return bool True if valid
     */
    public function validate(): bool
    {
        if ($this->bag === null) {
            $this->parse();
        }

        $requiredOptions = array_filter(
            $this->definedOptions,
            fn(Option $opt) => $opt->isRequired()
        );

        return $this->validator->validateBag($this->bag, $requiredOptions);
    }

    /**
     * Returns validation error messages collected by the validator.
     *
     * Responsibility: Returns validation error messages collected by the validator.
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validator->getErrors();
    }

    /**
     * Returns the validator used by this argument facade.
     *
     * Responsibility: Returns the validator used by this argument facade.
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    /**
     * Returns the parser used by this argument facade.
     *
     * Responsibility: Returns the parser used by this argument facade.
     * @return ArgumentParser
     */
    public function getParser(): ArgumentParser
    {
        return $this->parser;
    }

    /**
     * Returns the current parsed argument bag, if parsing has produced one.
     *
     * Responsibility: Returns the current parsed argument bag, if parsing has produced one.
     * @return ArgumentBag|null
     */
    public function getBag(): ?ArgumentBag
    {
        return $this->bag;
    }

    /**
     * Returns the original argv array stored in the parsed bag.
     *
     * Responsibility: Returns the original argv array stored in the parsed bag.
     * @return array
     */
    public function getRaw(): array
    {
        if ($this->bag === null) {
            $this->parse();
        }

        return $this->bag->getRaw();
    }

    /**
     * Determines whether the current PHP process is running in CLI mode.
     *
     * @return bool
     */
    public static function isCli(): bool
    {
        return php_sapi_name() === 'cli' || defined('STDIN');
    }

    /**
     * Returns the executable script basename from the current argv array.
     *
     * Responsibility: Returns the executable script basename from the current argv array.
     * @return string
     */
    public function getScriptName(): string
    {
        $argv = $_SERVER['argv'] ?? [];
        return basename($argv[0] ?? 'script');
    }
}
