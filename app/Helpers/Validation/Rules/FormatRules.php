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
