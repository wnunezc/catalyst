<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * @package   Catalyst
 * @subpackage Helpers\Validation
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 */

namespace Catalyst\Helpers\Validation;

/**
 * Validator — public API for the Catalyst validation system.
 *
 * Usage:
 *   $v = new Validator($data, $rules);
 *   if ($v->fails()) {
 *       return $this->jsonValidationError($v->errors());
 *   }
 *
 * Or via Controller helper:
 *   $v = $this->validate($data, $rules);
 *   $this->validateOrFail($data, $rules);  // throws ValidationException
 *
 * Rule string format:
 *   'field' => 'required|min:3|max:50|email'
 *   'field' => ['required', 'min:3', 'unique:users,email']
 *
 * @package Catalyst\Helpers\Validation
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
     * Determine whether validation fails.
     *
     * Runs validation on first call; subsequent calls use the cached result.
     *
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
     * @return bool
     */
    public function passes(): bool
    {
        return !$this->fails();
    }

    /**
     * Get all field-level errors.
     *
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
     * @return array<string, string[]>
     */
    public function getErrorsForJson(): array
    {
        return $this->errors();
    }

    /**
     * Run validation exactly once; cache the result.
     *
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
