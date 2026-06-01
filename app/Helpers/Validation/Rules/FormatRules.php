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
 * FormatRules — validation rules for format-based fields.
 *
 * Rules: email, url, date, boolean
 *
 * @package Catalyst\Helpers\Validation\Rules
 */
class FormatRules
{
    /**
     * The field must be a valid email address.
     *
     * @param mixed $value
     * @return bool
     */
    public static function email(mixed $value): bool
    {
        return filter_var((string) $value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * The field must be a valid URL.
     *
     * @param mixed $value
     * @return bool
     */
    public static function url(mixed $value): bool
    {
        return filter_var((string) $value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * The field must be a parseable date string.
     *
     * Accepts any format accepted by PHP's strtotime().
     *
     * @param mixed $value
     * @return bool
     */
    public static function date(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return strtotime((string) $value) !== false;
    }

    /**
     * The field must represent a boolean value.
     *
     * Accepted: true, false, 1, 0, '1', '0', 'true', 'false', 'yes', 'no', 'on', 'off'
     *
     * @param mixed $value
     * @return bool
     */
    public static function boolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
    }
}
