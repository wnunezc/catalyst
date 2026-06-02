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
 * ComparisonRules — validation rules comparing fields or value sets.
 *
 * Rules: same, different, confirmed, in, notIn
 *
 * @package Catalyst\Helpers\Validation\Rules
 * Responsibility: Validates relationships between fields and membership in allowed value sets.
 */
class ComparisonRules
{
    /**
     * The field must match another field in the data set.
     * Usage: same:other_field
     *
     * @param mixed                 $value  Current field value
     * @param string[]              $params [otherField]
     * @param array<string, mixed>  $data   Full data set
     * @return bool
     */
    public static function same(mixed $value, array $params, array $data): bool
    {
        $otherField = $params[0] ?? '';
        $otherValue = $data[$otherField] ?? null;
        return $value === $otherValue;
    }

    /**
     * The field must differ from another field in the data set.
     * Usage: different:other_field
     *
     * @param mixed                 $value  Current field value
     * @param string[]              $params [otherField]
     * @param array<string, mixed>  $data   Full data set
     * @return bool
     */
    public static function different(mixed $value, array $params, array $data): bool
    {
        $otherField = $params[0] ?? '';
        $otherValue = $data[$otherField] ?? null;
        return $value !== $otherValue;
    }

    /**
     * The field must match its confirmation counterpart ({field}_confirmation).
     * Usage: confirmed  (on field 'password' → checks 'password_confirmation')
     *
     * @param mixed $value        Current field value
     * @param mixed $confirmValue Value of the confirmation field
     * @return bool
     */
    public static function confirmed(mixed $value, mixed $confirmValue): bool
    {
        return $value === $confirmValue;
    }

    /**
     * The field must be one of the listed values.
     * Usage: in:admin,user,moderator
     *
     * @param mixed    $value
     * @param string[] $params Allowed values
     * @return bool
     */
    public static function in(mixed $value, array $params): bool
    {
        return in_array((string) $value, $params, strict: true);
    }

    /**
     * The field must NOT be one of the listed values.
     * Usage: not_in:banned,suspended
     *
     * @param mixed    $value
     * @param string[] $params Disallowed values
     * @return bool
     */
    public static function notIn(mixed $value, array $params): bool
    {
        return !in_array((string) $value, $params, strict: true);
    }
}
