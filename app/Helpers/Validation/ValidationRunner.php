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

use Catalyst\Helpers\Validation\Rules\{
    StringRules,
    NumericRules,
    FormatRules,
    ComparisonRules,
    UniqueRule,
    FileRules
};

/**
 * ValidationRunner — internal engine that applies rules to data.
 *
 * Iterates all field/rule pairs, resolves values (including dot-notation),
 * dispatches to the appropriate rule class, and collects error messages
 * via __() i18n helpers.
 *
 * @package Catalyst\Helpers\Validation
 * Responsibility: Applies parsed validation rules to input fields and collects localized errors.
 */
class ValidationRunner
{
    /**
     * Run all rules against the data and return field-level errors.
     *
     * Responsibility: Run all rules against the data and return field-level errors.
     * @param array<string, mixed>                        $data        Input data
     * @param array<string, array<array{0:string,1:string[]}>> $ruleMap Parsed rules per field
     * @param array<string, string>                       $labels      Optional field labels
     * @return array<string, string[]>                    Errors: ['field' => ['msg', ...]]
     */
    public function run(array $data, array $ruleMap, array $labels): array
    {
        $errors = [];

        foreach ($ruleMap as $field => $parsedRules) {
            $value = $this->dataGet($data, $field);
            $label = $labels[$field] ?? ucfirst(str_replace(['_', '.'], ' ', $field));

            $hasRequired = $this->hasRule('required', $parsedRules);

            foreach ($parsedRules as [$ruleName, $params]) {
                // Non-required rules are skipped for empty values when the
                // field is optional (no 'required' rule declared).
                if ($ruleName !== 'required' && !$hasRequired && $this->isEmpty($value)) {
                    continue;
                }

                $message = $this->applyRule($ruleName, $field, $label, $value, $params, $data);

                if ($message !== null) {
                    $errors[$field][] = $message;
                }
            }
        }

        return $errors;
    }

    /**
     * Dispatch a single rule and return the error message, or null on pass.
     *
     * Responsibility: Dispatch a single rule and return the error message, or null on pass.
     * @param string               $rule   Rule name
     * @param string               $field  Field key
     * @param string               $label  Human-readable label
     * @param mixed                $value  Field value
     * @param string[]             $params Rule parameters
     * @param array<string, mixed> $data   Full data set (for cross-field rules)
     * @return string|null Error message, or null if the rule passes
     */
    private function applyRule(
        string $rule,
        string $field,
        string $label,
        mixed  $value,
        array  $params,
        array  $data
    ): ?string {
        return match ($rule) {
            // -- String rules --------------------------------------------
            'required'  => StringRules::required($value)
                ? null
                : __('validation.required', ['field' => $label]),

            'min'       => StringRules::min($value, $params)
                ? null
                : __('validation.min', ['field' => $label, 'min' => $params[0] ?? 0]),

            'max'       => StringRules::max($value, $params)
                ? null
                : __('validation.max', ['field' => $label, 'max' => $params[0] ?? 0]),

            'alpha'     => StringRules::alpha($value)
                ? null
                : __('validation.alpha', ['field' => $label]),

            'alpha_num' => StringRules::alphaNum($value)
                ? null
                : __('validation.alpha_num', ['field' => $label]),

            'regex'     => StringRules::regex($value, $params)
                ? null
                : __('validation.regex', ['field' => $label]),

            // -- Numeric rules --------------------------------------------
            'numeric'   => NumericRules::numeric($value)
                ? null
                : __('validation.numeric', ['field' => $label]),

            'integer'   => NumericRules::integer($value)
                ? null
                : __('validation.integer', ['field' => $label]),

            'min_value' => NumericRules::minValue($value, $params)
                ? null
                : __('validation.min_value', ['field' => $label, 'min' => $params[0] ?? 0]),

            'max_value' => NumericRules::maxValue($value, $params)
                ? null
                : __('validation.max_value', ['field' => $label, 'max' => $params[0] ?? 0]),

            'between'   => NumericRules::between($value, $params)
                ? null
                : __('validation.between', [
                    'field' => $label,
                    'min'   => $params[0] ?? 0,
                    'max'   => $params[1] ?? 0,
                ]),

            // -- Format rules ---------------------------------------------
            'email'     => FormatRules::email($value)
                ? null
                : __('validation.email', ['field' => $label]),

            'url'       => FormatRules::url($value)
                ? null
                : __('validation.url', ['field' => $label]),

            'date'      => FormatRules::date($value)
                ? null
                : __('validation.date', ['field' => $label]),

            'boolean'   => FormatRules::boolean($value)
                ? null
                : __('validation.boolean', ['field' => $label]),

            // -- Comparison rules -----------------------------------------
            'same'      => ComparisonRules::same($value, $params, $data)
                ? null
                : __('validation.same', ['field' => $label, 'other' => $params[0] ?? '']),

            'different' => ComparisonRules::different($value, $params, $data)
                ? null
                : __('validation.different', ['field' => $label, 'other' => $params[0] ?? '']),

            'confirmed' => ComparisonRules::confirmed(
                $value,
                $this->dataGet($data, $field . '_confirmation')
            )
                ? null
                : __('validation.confirmed', ['field' => $label]),

            'in'        => ComparisonRules::in($value, $params)
                ? null
                : __('validation.in', ['field' => $label]),

            'not_in'    => ComparisonRules::notIn($value, $params)
                ? null
                : __('validation.not_in', ['field' => $label]),

            // -- DB rule --------------------------------------------------
            'unique'    => UniqueRule::passes($value, $params)
                ? null
                : __('validation.unique', ['field' => $label]),

            // -- File rules -----------------------------------------------
            'file'          => FileRules::file($value)
                ? null
                : __('validation.file', ['field' => $label]),

            'mimes'         => FileRules::mimes($value, $params)
                ? null
                : __('validation.mimes', [
                    'field' => $label,
                    'types' => implode(', ', $params),
                ]),

            'max_size'      => FileRules::maxSize($value, $params)
                ? null
                : __('validation.max_size', ['field' => $label, 'max' => $params[0] ?? 0]),

            'max_file_size' => FileRules::maxFileSize($field, $params)
                ? null
                : __('validation.max_file_size', ['field' => $label, 'max' => $params[0] ?? 0]),

            'mime_types'    => FileRules::mimeTypes($value, $params)
                ? null
                : __('validation.mime_types', [
                    'field' => $label,
                    'types' => implode(', ', $params),
                ]),

            // Unknown rule — skip silently
            default => null,
        };
    }

    /**
     * Retrieve a value from a nested array using dot notation. E.g. 'address.city' → $data['address']['city'].
     *
     * Responsibility: Retrieve a value from a nested array using dot notation. E.g. 'address.city' → $data['address']['city'].
     * @param array<string, mixed> $data
     * @param string               $key  Dot-notation key
     * @return mixed
     */
    private function dataGet(array $data, string $key): mixed
    {
        if (!str_contains($key, '.')) {
            return $data[$key] ?? null;
        }

        $current = $data;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * Check whether a specific rule name is present in the parsed rules list.
     *
     * Responsibility: Check whether a specific rule name is present in the parsed rules list.
     * @param string                          $ruleName
     * @param array<array{0:string,1:string[]}> $parsedRules
     * @return bool
     */
    private function hasRule(string $ruleName, array $parsedRules): bool
    {
        foreach ($parsedRules as [$name]) {
            if ($name === $ruleName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether a value is considered empty.
     *
     * Responsibility: Determine whether a value is considered empty.
     * @param mixed $value
     * @return bool
     */
    private function isEmpty(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_array($value) && count($value) === 0) {
            return true;
        }

        return false;
    }
}
