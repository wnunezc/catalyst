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

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

/**
 * UniqueRule — validates that a value does not already exist in a DB column.
 *
 * Usage: unique:table,column[,ignoreValue,ignoreColumn]
 *
 * Examples:
 *   'email' => 'required|email|unique:users,email'
 *   'email' => 'required|email|unique:users,email,5,id'  (ignore row where id=5)
 *
 * Fails silently (returns true) if the database is unavailable — validation
 * continues for all other rules, and a warning is logged.
 *
 * @package Catalyst\Helpers\Validation\Rules
 */
class UniqueRule
{
    /**
     * Check that $value does not exist in the specified table/column.
     *
     * @param mixed    $value  The field value to check
     * @param string[] $params [table, column, ignoreValue?, ignoreColumn?]
     * @return bool True if the value is unique (passes), false if it already exists
     */
    public static function passes(mixed $value, array $params): bool
    {
        $table  = $params[0] ?? '';
        $column = $params[1] ?? '';

        if ($table === '' || $column === '') {
            return true;
        }

        try {
            $qb = DatabaseManager::getInstance()
                ->table($table)
                ->whereEqual($column, $value);

            // Support ignoring a specific row (e.g. on update: unique:users,email,5,id)
            if (isset($params[2], $params[3])) {
                $qb = $qb->where($params[3], '!=', $params[2]);
            }

            return $qb->count() === 0;
        } catch (Throwable $e) {
            try {
                Logger::getInstance()->warning(
                    'UniqueRule: DB unavailable, skipping uniqueness check.',
                    ['table' => $table, 'column' => $column, 'error' => $e->getMessage()]
                );
            } catch (Throwable) {
                // Logger unavailable (e.g. CLI without bootstrap) — silently skip
            }

            return true;
        }
    }
}
