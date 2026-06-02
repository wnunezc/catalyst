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

namespace Catalyst\Helpers\Validation;

/**
 * Validator — public API for the Catalyst validation system.
 *
 * @package Catalyst\Helpers\Validation
 * Responsibility: Exposes lazy validation results and field-level error collections to callers.
 */
class Validator
{
    /**
     * Field-level errors collected after running validation.
     * Structure: ['field' => ['message1', 'message2', ...]]
     *
     * @var array<string, string[]>
     */
    private array $errors = [];

    /**
     * Whether validation has been run yet.
     *
     * @var bool
     */
    private bool $ran = false;

    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param array<string, mixed>                    $data   Input data to validate
     * @param array<string, string|array<int,string>> $rules  Rules per field
     * @param array<string, string>                   $labels Optional human-readable labels
     */
    public function __construct(
        private readonly array $data,
        private readonly array $rules,
        private readonly array $labels = []
    ) {
    }

    /**
     * Determine whether validation fails. Runs validation on first call; subsequent calls use the cached result.
     *
     * Responsibility: Determine whether validation fails. Runs validation on first call; subsequent calls use the cached result.
     * @return bool
     */
    public function fails(): bool
    {
        $this->runOnce();
        return count($this->errors) > 0;
    }

    /**
     * Determine whether validation passes.
     *
     * Responsibility: Determine whether validation passes.
     * @return bool
     */
    public function passes(): bool
    {
        return !$this->fails();
    }

    /**
     * Get all field-level errors.
     *
     * Responsibility: Get all field-level errors.
     * @return array<string, string[]>
     */
    public function errors(): array
    {
        $this->runOnce();
        return $this->errors;
    }

    /**
     * Get the first error message per field.
     *
     * Responsibility: Exposes the first validation message for each field so forms can show concise feedback.
     * @return array<string, string>
     */
    public function firstErrors(): array
    {
        $this->runOnce();

        $first = [];

        foreach ($this->errors as $field => $messages) {
            $first[$field] = $messages[0];
        }

        return $first;
    }

    /**
     * Alias for errors() — compatible with jsonValidationError() format.
     *
     * Responsibility: Alias for errors() — compatible with jsonValidationError() format.
     * @return array<string, string[]>
     */
    public function getErrorsForJson(): array
    {
        return $this->errors();
    }

    /**
     * Run validation exactly once; cache the result.
     *
     * Responsibility: Run validation exactly once; cache the result.
     * @return void
     */
    private function runOnce(): void
    {
        if ($this->ran) {
            return;
        }

        $this->ran = true;

        $parser  = new RuleParser();
        $ruleMap = [];

        foreach ($this->rules as $field => $fieldRules) {
            $ruleMap[$field] = $parser->parse($fieldRules);
        }

        $this->errors = (new ValidationRunner())->run($this->data, $ruleMap, $this->labels);
    }
}
