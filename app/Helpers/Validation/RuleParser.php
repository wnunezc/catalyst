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
 * RuleParser — parses rule definitions into a normalized structure.
 *
 * Supports two input forms:
 *   string: 'required|min:3|unique:users,email'
 *   array:  ['required', 'min:3', 'unique:users,email']
 *
 * Returns an array of [ruleName, params] tuples:
 *   [['required', []], ['min', ['3']], ['unique', ['users', 'email']]]
 *
 * @package Catalyst\Helpers\Validation
 * Responsibility: Normalizes string or array validation definitions into rule-and-parameter tuples.
 */
class RuleParser
{
    /**
     * Parse a rule definition into normalized tuples.
     *
     * Responsibility: Parse a rule definition into normalized tuples.
     * @param string|array<int, string> $rules Rule definition (string or array)
     * @return array<int, array{0: string, 1: string[]}> Normalized rule tuples
     */
    public function parse(string|array $rules): array
    {
        $ruleList = is_string($rules)
            ? explode('|', $rules)
            : $rules;

        $parsed = [];

        foreach ($ruleList as $rule) {
            $rule = trim((string) $rule);

            if ($rule === '') {
                continue;
            }

            if (str_contains($rule, ':')) {
                [$name, $paramString] = explode(':', $rule, 2);
                $params = array_map('trim', explode(',', $paramString));
            } else {
                $name   = $rule;
                $params = [];
            }

            $parsed[] = [trim($name), $params];
        }

        return $parsed;
    }
}
