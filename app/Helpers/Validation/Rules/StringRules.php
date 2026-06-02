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

namespace Catalyst\Helpers\Validation\Rules;

/**
 * StringRules — validation rules for string fields.
 *
 * Rules: required, min, max, alpha, alphaNum, regex
 *
 * @package Catalyst\Helpers\Validation\Rules
 * Responsibility: Validates required values, string lengths, character sets and patterns.
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
