<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database;

use InvalidArgumentException;

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

    public static function assertTable(string $table): string
    {
        return self::assertAliasedReference($table, 'table');
    }

    public static function assertColumn(string $column, string $context = 'column'): string
    {
        $column = trim($column);

        if (preg_match('/^' . self::QUALIFIED_OR_STAR . '$/', $column) === 1) {
            return $column;
        }

        throw new InvalidArgumentException("Invalid SQL {$context} reference: {$column}");
    }

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

    public static function assertOperator(string $operator): string
    {
        $normalized = strtoupper(trim($operator));

        if (in_array($normalized, self::COMPARISON_OPERATORS, true)) {
            return $normalized;
        }

        throw new InvalidArgumentException("Invalid SQL operator: {$operator}");
    }

    public static function assertJoinOperator(string $operator): string
    {
        return self::assertOperator($operator);
    }

    public static function assertJoinType(string $type): string
    {
        $normalized = strtoupper(trim($type));

        if (in_array($normalized, ['INNER', 'LEFT', 'RIGHT'], true)) {
            return $normalized;
        }

        throw new InvalidArgumentException("Invalid SQL join type: {$type}");
    }

    public static function normalizeDirection(string $direction): string
    {
        $normalized = strtoupper(trim($direction));

        if (in_array($normalized, ['ASC', 'DESC'], true)) {
            return $normalized;
        }

        throw new InvalidArgumentException("Invalid ORDER BY direction: {$direction}");
    }

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
