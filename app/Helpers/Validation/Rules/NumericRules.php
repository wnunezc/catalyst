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
 * NumericRules — validation rules for numeric fields.
 *
 * Rules: numeric, integer, minValue, maxValue, between
 *
 * @package Catalyst\Helpers\Validation\Rules
 */
class NumericRules
{
    /**
     * The field must be a numeric value (int or float).
     *
     * @param mixed $value
     * @return bool
     */
    public static function numeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * The field must be a valid integer.
     *
     * @param mixed $value
     * @return bool
     */
    public static function integer(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * The field must be at least $params[0] (numeric comparison).
     *
     * @param mixed    $value
     * @param string[] $params [min]
     * @return bool
     */
    public static function minValue(mixed $value, array $params): bool
    {
        if (!is_numeric($value) || !isset($params[0])) {
            return false;
        }

        return (float) $value >= (float) $params[0];
    }

    /**
     * The field must not exceed $params[0] (numeric comparison).
     *
     * @param mixed    $value
     * @param string[] $params [max]
     * @return bool
     */
    public static function maxValue(mixed $value, array $params): bool
    {
        if (!is_numeric($value) || !isset($params[0])) {
            return false;
        }

        return (float) $value <= (float) $params[0];
    }

    /**
     * The field must be between $params[0] and $params[1] inclusive.
     *
     * @param mixed    $value
     * @param string[] $params [min, max]
     * @return bool
     */
    public static function between(mixed $value, array $params): bool
    {
        if (!is_numeric($value) || !isset($params[0], $params[1])) {
            return false;
        }

        $val = (float) $value;
        return $val >= (float) $params[0] && $val <= (float) $params[1];
    }
}
