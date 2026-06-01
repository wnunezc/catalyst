<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * @package   Catalyst
 * @subpackage Helpers\Validation\Rules
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 */

namespace Catalyst\Helpers\Validation\Rules;

/**
 * StringRules — validation rules for string fields.
 *
 * Rules: required, min, max, alpha, alphaNum, regex
 *
 * @package Catalyst\Helpers\Validation\Rules
 */
class StringRules
{
    /**
     * The field must not be empty.
     *
     * @param mixed $value
     * @return bool
     */
    public static function required(mixed $value): bool
    {
        if ($value === null || $value === false) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return true;
    }

    /**
     * The field must have at least $params[0] characters.
     *
     * @param mixed         $value
     * @param string[]      $params [minLength]
     * @return bool
     */
    public static function min(mixed $value, array $params): bool
    {
        $min = isset($params[0]) ? (int) $params[0] : 0;
        return mb_strlen((string) $value) >= $min;
    }

    /**
     * The field may not exceed $params[0] characters.
     *
     * @param mixed         $value
     * @param string[]      $params [maxLength]
     * @return bool
     */
    public static function max(mixed $value, array $params): bool
    {
        $max = isset($params[0]) ? (int) $params[0] : PHP_INT_MAX;
        return mb_strlen((string) $value) <= $max;
    }

    /**
     * The field may only contain letters (a–z, A–Z).
     *
     * @param mixed $value
     * @return bool
     */
    public static function alpha(mixed $value): bool
    {
        return (bool) preg_match('/^[a-zA-Z]+$/', (string) $value);
    }

    /**
     * The field may only contain letters and numbers.
     *
     * @param mixed $value
     * @return bool
     */
    public static function alphaNum(mixed $value): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9]+$/', (string) $value);
    }

    /**
     * The field must match the given regular expression.
     *
     * @param mixed    $value
     * @param string[] $params [pattern]
     * @return bool
     */
    public static function regex(mixed $value, array $params): bool
    {
        if (empty($params[0])) {
            return false;
        }

        return (bool) preg_match($params[0], (string) $value);
    }
}
