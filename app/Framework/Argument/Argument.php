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

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Main CLI argument handling class
 *
 * Provides a unified interface for accessing command-line arguments
 * Used by FileOutput and other CLI components
 *
 * @package Catalyst\Framework\Argument
 */
class Argument
{
    use SingletonTrait;

    /**
     * Parsed argument bag
     */
    private ?ArgumentBag $bag = null;

    /**
     * Argument parser
     */
    private ArgumentParser $parser;

    /**
     * Argument validator
     */
    private Validator $validator;

    /**
     * Predefined options schema
     *
     * @var array<Option>
     */
    private array $definedOptions = [];

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->parser = new ArgumentParser();
        $this->validator = new Validator();

        // Auto-parse on instantiation
        $this->parse();
    }

    /**
     * Parse command line arguments
     *
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
     * Get all arguments as associative array
     *
     * Compatible with FileOutput.php usage: $arguments = (new Argument)->getArguments();
     *
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
     * Check if an option exists
     *
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
     * Get option value
     *
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
     * Get parameter value by position
     *
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
     * Get all options
     *
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
     * Get all parameters
     *
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
     * Define an option schema
     *
     * @param Option $option Option definition
     * @return self
     */
    public function defineOption(Option $option): self
    {
        $this->definedOptions[] = $option;
        return $this;
    }

    /**
     * Define multiple option schemas
     *
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
     * Validate arguments
     *
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
     * Get validation errors
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validator->getErrors();
    }

    /**
     * Get validator instance
     *
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    /**
     * Get parser instance
     *
     * @return ArgumentParser
     */
    public function getParser(): ArgumentParser
    {
        return $this->parser;
    }

    /**
     * Get argument bag
     *
     * @return ArgumentBag|null
     */
    public function getBag(): ?ArgumentBag
    {
        return $this->bag;
    }

    /**
     * Get raw argv
     *
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
     * Check if running in CLI mode
     *
     * @return bool
     */
    public static function isCli(): bool
    {
        return php_sapi_name() === 'cli' || defined('STDIN');
    }

    /**
     * Get script name
     *
     * @return string
     */
    public function getScriptName(): string
    {
        $argv = $_SERVER['argv'] ?? [];
        return basename($argv[0] ?? 'script');
    }
}
