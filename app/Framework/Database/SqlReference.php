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

namespace Catalyst\Framework\Database;

use InvalidArgumentException;

/**
 * Defines the Sql Reference class contract.
 *
 * @package Catalyst\Framework\Database
 * Responsibility: Coordinates the sql reference behavior within its module boundary.
 */
final class SqlReference
{
    private const IDENTIFIER = '[A-Za-z_][A-Za-z0-9_]*';
    private const QUALIFIED_IDENTIFIER = self::IDENTIFIER . '(?:\.' . self::IDENTIFIER . ')*';
    private const QUALIFIED_OR_STAR = self::IDENTIFIER . '(?:\.(?:' . self::IDENTIFIER . '|\*))?';
    private const SELECT_FUNCTION = '[A-Z_][A-Z0-9_]*\(\s*(?:\*|' . self::QUALIFIED_IDENTIFIER . ')\s*\)';

    private const COMPARISON_OPERATORS = [
        '=',
        '!=',
        '<>',
        '<',
        '>',
        '<=',
        '>=',
        'LIKE',
        'NOT LIKE',
    ];

    /**
     * Handles the assert table workflow.
     */
    public static function assertTable(string $table): string
    {
        return self::assertAliasedReference($table, 'table');
    }

    /**
     * Handles the assert column workflow.
     */
    public static function assertColumn(string $column, string $context = 'column'): string
    {
        $column = trim($column);

        if (preg_match('/^' . self::QUALIFIED_OR_STAR . '$/', $column) === 1) {
            return $column;
        }

        throw new InvalidArgumentException("Invalid SQL {$context} reference: {$column}");
    }

    /**
     * Handles the assert selectable workflow.
     */
    public static function assertSelectable(string $column): string
    {
        $column = trim($column);

        if ($column === '*') {
            return $column;
        }

        if (preg_match('/^(?:DISTINCT\s+)?' . self::QUALIFIED_OR_STAR . '$/i', $column) === 1) {
            return $column;
        }

        if (preg_match(
            '/^' . self::SELECT_FUNCTION . '(?:\s+AS\s+' . self::IDENTIFIER . ')?$/i',
            $column
        ) === 1) {
            return $column;
        }

        throw new InvalidArgumentException("Invalid SQL select expression: {$column}");
    }

    /**
     * Handles the assert operator workflow.
     */
    public static function assertOperator(string $operator): string
    {
        $normalized = strtoupper(trim($operator));

        if (in_array($normalized, self::COMPARISON_OPERATORS, true)) {
            return $normalized;
        }

        throw new InvalidArgumentException("Invalid SQL operator: {$operator}");
    }

    /**
     * Handles the assert join operator workflow.
     */
    public static function assertJoinOperator(string $operator): string
    {
        return self::assertOperator($operator);
    }

    /**
     * Handles the assert join type workflow.
     */
    public static function assertJoinType(string $type): string
    {
        $normalized = strtoupper(trim($type));

        if (in_array($normalized, ['INNER', 'LEFT', 'RIGHT'], true)) {
            return $normalized;
        }

        throw new InvalidArgumentException("Invalid SQL join type: {$type}");
    }

    /**
     * Normalizes the provided value.
     */
    public static function normalizeDirection(string $direction): string
    {
        $normalized = strtoupper(trim($direction));

        if (in_array($normalized, ['ASC', 'DESC'], true)) {
            return $normalized;
        }

        throw new InvalidArgumentException("Invalid ORDER BY direction: {$direction}");
    }

    /**
     * Handles the assert aliased reference workflow.
     */
    private static function assertAliasedReference(string $reference, string $context): string
    {
        $reference = trim($reference);

        if (preg_match(
            '/^' . self::QUALIFIED_IDENTIFIER . '(?:\s+(?:AS\s+)?' . self::IDENTIFIER . ')?$/i',
            $reference
        ) === 1) {
            return $reference;
        }

        throw new InvalidArgumentException("Invalid SQL {$context} reference: {$reference}");
    }
}
