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

use Catalyst\Helpers\Exceptions\QueryException;

/**
 * Fluent SQL query builder
 *
 * Provides a fluent interface for building and executing SQL queries
 * against a database Connection.
 *
 * @package Catalyst\Framework\Database
 * Responsibility: Builds validated SQL clauses, bindings and aggregate statements for a table.
 */
class QueryBuilder
{
    protected Connection $connection;

    protected string $table;

    protected array $columns = ['*'];

    protected array $wheres = [];

    protected array $orders = [];

    protected array $groups = [];

    protected array $havings = [];

    protected array $joins = [];

    protected ?int $limit = null;

    protected ?int $offset = null;

    /**
     * Initializes the Query Builder instance.
     *
     * Responsibility: Initializes the Query Builder instance.
     */
    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = SqlReference::assertTable($table);
    }

    // -------------------------------------------------------------------------
    // Column selection
    // -------------------------------------------------------------------------

    /**
     * Sets the columns or expressions selected by the query.
     *
     * Responsibility: Sets the columns or expressions selected by the query.
     */
    public function select(array|string $columns = ['*']): self
    {
        $rawColumns = is_array($columns) ? $columns : func_get_args();
        $this->columns = array_map(
            static fn (string $column): string => SqlReference::assertSelectable($column),
            $rawColumns
        );
        return $this;
    }

    // -------------------------------------------------------------------------
    // WHERE clauses
    // -------------------------------------------------------------------------

    /**
     * Adds a basic WHERE condition with a validated column and operator.
     *
     * Responsibility: Adds a basic WHERE condition with a validated column and operator.
     */
    public function where(string $column, string $operator, mixed $value, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type'     => 'basic',
            'column'   => SqlReference::assertColumn($column),
            'operator' => SqlReference::assertOperator($operator),
            'value'    => $value,
            'boolean'  => $boolean,
        ];
        return $this;
    }

    /**
     * Adds a basic OR WHERE condition.
     *
     * Responsibility: Adds a basic OR WHERE condition.
     */
    public function orWhere(string $column, string $operator, mixed $value): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Adds an equality WHERE condition.
     *
     * Responsibility: Adds an equality WHERE condition.
     */
    public function whereEqual(string $column, mixed $value): self
    {
        return $this->where($column, '=', $value);
    }

    /**
     * Adds a WHERE IN condition with bound values.
     *
     * Responsibility: Adds a WHERE IN condition with bound values.
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type'    => 'in',
            'column'  => SqlReference::assertColumn($column),
            'values'  => $values,
            'boolean' => $boolean,
        ];
        return $this;
    }

    /**
     * Adds an OR WHERE IN condition.
     *
     * Responsibility: Adds an OR WHERE IN condition.
     */
    public function orWhereIn(string $column, array $values): self
    {
        return $this->whereIn($column, $values, 'OR');
    }

    /**
     * Adds a NULL or NOT NULL WHERE condition.
     *
     * Responsibility: Adds a NULL or NOT NULL WHERE condition.
     */
    public function whereNull(string $column, string $boolean = 'AND', bool $not = false): self
    {
        $this->wheres[] = [
            'type'    => 'null',
            'column'  => SqlReference::assertColumn($column),
            'boolean' => $boolean,
            'not'     => $not,
        ];
        return $this;
    }

    /**
     * Adds a NOT NULL WHERE condition.
     *
     * Responsibility: Adds a NOT NULL WHERE condition.
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        return $this->whereNull($column, $boolean, true);
    }

    // -------------------------------------------------------------------------
    // ORDER, GROUP, HAVING, JOIN
    // -------------------------------------------------------------------------

    /**
     * Adds an ORDER BY clause with a validated direction.
     *
     * Responsibility: Adds an ORDER BY clause with a validated direction.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = [
            'column' => SqlReference::assertColumn($column, 'order column'),
            'direction' => SqlReference::normalizeDirection($direction),
        ];
        return $this;
    }

    /**
     * Adds one or more GROUP BY columns.
     *
     * Responsibility: Adds one or more GROUP BY columns.
     */
    public function groupBy(array|string $columns): self
    {
        $groupColumns = is_array($columns) ? $columns : [$columns];
        $this->groups = array_merge(
            $this->groups,
            array_map(
                static fn (string $column): string => SqlReference::assertColumn($column, 'group column'),
                $groupColumns
            )
        );
        return $this;
    }

    /**
     * Adds a HAVING condition with a validated column and operator.
     *
     * Responsibility: Adds a HAVING condition with a validated column and operator.
     */
    public function having(string $column, string $operator, mixed $value, string $boolean = 'AND'): self
    {
        $this->havings[] = [
            'column'   => SqlReference::assertColumn($column, 'having column'),
            'operator' => SqlReference::assertOperator($operator),
            'value'    => $value,
            'boolean'  => $boolean,
        ];
        return $this;
    }

    /**
     * Adds a validated JOIN clause.
     *
     * Responsibility: Adds a validated JOIN clause.
     */
    public function join(
        string $table,
        string $first,
        string $operator,
        string $second,
        string $type = 'INNER'
    ): self {
        $this->joins[] = [
            'table'    => SqlReference::assertTable($table),
            'first'    => SqlReference::assertColumn($first, 'join reference'),
            'operator' => SqlReference::assertJoinOperator($operator),
            'second'   => SqlReference::assertColumn($second, 'join reference'),
            'type'     => SqlReference::assertJoinType($type),
        ];
        return $this;
    }

    /**
     * Adds a LEFT JOIN clause.
     *
     * Responsibility: Adds a LEFT JOIN clause.
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Adds a RIGHT JOIN clause.
     *
     * Responsibility: Adds a RIGHT JOIN clause.
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    // -------------------------------------------------------------------------
    // LIMIT / OFFSET / pagination
    // -------------------------------------------------------------------------

    /**
     * Sets the maximum number of rows returned by the query.
     *
     * Responsibility: Sets the maximum number of rows returned by the query.
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Sets the row offset used by the query.
     *
     * Responsibility: Sets the row offset used by the query.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Applies offset and limit values for page-based pagination.
     *
     * Responsibility: Applies offset and limit values for page-based pagination.
     */
    public function forPage(int $page, int $perPage): self
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    // -------------------------------------------------------------------------
    // Execution — SELECT
    // -------------------------------------------------------------------------

    /**
     * Execute and return the first matching row, or null. Return type is `mixed` to allow ModelQueryBuilder to narrow the return type to `?Model` via PHP 8 covariant return types without violating the Liskov Substitution Principle at the PHP engine level. Direct callers of QueryBuilder (not ModelQueryBuilder) always receive `?array` at runtime; the broadened signature is a type-system necessity.
     *
     * Responsibility: Execute and return the first matching row, or null. Return type is `mixed` to allow ModelQueryBuilder to narrow the return type to `?Model` via PHP 8 covariant return types without violating the Liskov Substitution Principle at the PHP engine level. Direct callers of QueryBuilder (not ModelQueryBuilder) always receive `?array` at runtime; the broadened signature is a type-system necessity.
     * @throws QueryException
     */
    public function first(array $columns = ['*']): mixed
    {
        if ($columns !== ['*']) {
            $this->columns = $columns;
        }
        return $this->limit(1)->get()[0] ?? null;
    }

    /**
     * Execute and return all matching rows. Return type is `mixed` to allow ModelQueryBuilder to narrow the return type to `Collection` via PHP 8 covariant return types. Direct callers of QueryBuilder always receive `array` at runtime.
     *
     * Responsibility: Execute and return all matching rows. Return type is `mixed` to allow ModelQueryBuilder to narrow the return type to `Collection` via PHP 8 covariant return types. Direct callers of QueryBuilder always receive `array` at runtime.
     * @throws QueryException
     */
    public function get(array $columns = ['*']): mixed
    {
        if ($columns !== ['*']) {
            $this->columns = $columns;
        }
        [$query, $bindings] = $this->compileSelect();
        return $this->connection->select($query, $bindings);
    }

    /**
     * Fetches all matching rows through the query builder get() execution path.
     *
     * Responsibility: Provides the all() alias while preserving query execution and exception behavior from get().
     *
     * @throws QueryException
     */
    public function all(array $columns = ['*']): mixed
    {
        return $this->get($columns);
    }

    /**
     * Return the count of matching rows.
     *
     * Responsibility: Return the count of matching rows.
     * @throws QueryException
     */
    public function count(): int
    {
        $result = $this->aggregate('COUNT');
        return (int)($result['aggregate'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Execution — INSERT / UPDATE / DELETE
    // -------------------------------------------------------------------------

    /**
     * Insert a new row and return the last insert ID.
     *
     * Responsibility: Insert a new row and return the last insert ID.
     * @throws QueryException
     */
    public function insert(array $values): int
    {
        return $this->connection->insert($this->table, $values);
    }

    /**
     * Update matching rows and return the number of affected rows.
     *
     * Responsibility: Update matching rows and return the number of affected rows.
     * @throws QueryException
     */
    public function update(array $values): int
    {
        [$query, $bindings] = $this->compileUpdate($values);
        return $this->connection->execute($query, $bindings);
    }

    /**
     * Delete matching rows and return the number of affected rows.
     *
     * Responsibility: Delete matching rows and return the number of affected rows.
     * @throws QueryException
     */
    public function delete(): int
    {
        [$query, $bindings] = $this->compileDelete();
        return $this->connection->execute($query, $bindings);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns the database connection used by this builder.
     *
     * Responsibility: Returns the database connection used by this builder.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    // -------------------------------------------------------------------------
    // SQL compilation (protected)
    // -------------------------------------------------------------------------

    /**
     * Compiles the current SELECT query and bindings.
     *
     * Responsibility: Compiles the current SELECT query and bindings.
     */
    protected function compileSelect(): array
    {
        $parts    = ['SELECT ' . $this->compileColumns(), 'FROM ' . $this->table];
        $bindings = [];

        foreach ($this->joins as $join) {
            $parts[] = "{$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        if (!empty($this->wheres)) {
            [$whereStr, $whereBindings] = $this->compileWheres();
            $parts[]  = 'WHERE ' . $whereStr;
            $bindings = array_merge($bindings, $whereBindings);
        }

        if (!empty($this->groups)) {
            $parts[] = 'GROUP BY ' . implode(', ', $this->groups);
        }

        if (!empty($this->havings)) {
            [$havingStr, $havingBindings] = $this->compileHavings();
            $parts[]  = 'HAVING ' . $havingStr;
            $bindings = array_merge($bindings, $havingBindings);
        }

        if (!empty($this->orders)) {
            $parts[] = 'ORDER BY ' . $this->compileOrders();
        }

        if ($this->limit !== null) {
            $parts[] = "LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $parts[] = "OFFSET {$this->offset}";
        }

        return [implode(' ', $parts), $bindings];
    }

    /**
     * Compiles the selected column list.
     *
     * Responsibility: Compiles the selected column list.
     */
    protected function compileColumns(): string
    {
        return implode(', ', array_map(
            static fn (string $column): string => SqlReference::assertSelectable($column),
            $this->columns
        ));
    }

    /**
     * Compiles WHERE clauses and their bound values.
     *
     * Responsibility: Compiles WHERE clauses and their bound values.
     */
    protected function compileWheres(): array
    {
        $parts    = [];
        $bindings = [];

        foreach ($this->wheres as $i => $where) {
            $prefix = $i === 0 ? '' : $where['boolean'] . ' ';

            if ($where['type'] === 'basic') {
                $key            = 'param_' . count($bindings);
                $parts[]        = $prefix . "{$where['column']} {$where['operator']} :$key";
                $bindings[$key] = $where['value'];
            } elseif ($where['type'] === 'in') {
                $placeholders = [];
                foreach ($where['values'] as $value) {
                    $key              = 'param_' . count($bindings);
                    $placeholders[]   = ":$key";
                    $bindings[$key]   = $value;
                }
                $parts[] = $prefix . "{$where['column']} IN (" . implode(', ', $placeholders) . ')';
            } elseif ($where['type'] === 'null') {
                $parts[] = $prefix . "{$where['column']} " . ($where['not'] ? 'IS NOT NULL' : 'IS NULL');
            }
        }

        return [implode(' ', $parts), $bindings];
    }

    /**
     * Compiles ORDER BY clauses.
     *
     * Responsibility: Compiles ORDER BY clauses.
     */
    protected function compileOrders(): string
    {
        $parts = [];
        foreach ($this->orders as $order) {
            $parts[] = "{$order['column']} {$order['direction']}";
        }
        return implode(', ', $parts);
    }

    /**
     * Compiles HAVING clauses and their bound values.
     *
     * Responsibility: Compiles HAVING clauses and their bound values.
     */
    protected function compileHavings(): array
    {
        $parts    = [];
        $bindings = [];

        foreach ($this->havings as $i => $having) {
            $prefix         = $i === 0 ? '' : $having['boolean'] . ' ';
            $key            = 'having_' . count($bindings);
            $parts[]        = $prefix . "{$having['column']} {$having['operator']} :$key";
            $bindings[$key] = $having['value'];
        }

        return [implode(' ', $parts), $bindings];
    }

    /**
     * Compiles an UPDATE statement and bindings for matching rows.
     *
     * Responsibility: Compiles an UPDATE statement and bindings for matching rows.
     */
    protected function compileUpdate(array $values): array
    {
        $sets     = [];
        $bindings = [];

        foreach ($values as $column => $value) {
            $column = SqlReference::assertColumn((string) $column, 'update column');
            $key            = 'set_' . count($bindings);
            $sets[]         = "$column = :$key";
            $bindings[$key] = $value;
        }

        $parts = ['UPDATE ' . $this->table . ' SET ' . implode(', ', $sets)];

        if (!empty($this->wheres)) {
            [$whereStr, $whereBindings] = $this->compileWheres();
            $parts[]  = 'WHERE ' . $whereStr;
            $bindings = array_merge($bindings, $whereBindings);
        }

        return [implode(' ', $parts), $bindings];
    }

    /**
     * Compiles a DELETE statement and bindings for matching rows.
     *
     * Responsibility: Compiles a DELETE statement and bindings for matching rows.
     */
    protected function compileDelete(): array
    {
        $parts    = ['DELETE FROM ' . $this->table];
        $bindings = [];

        if (!empty($this->wheres)) {
            [$whereStr, $whereBindings] = $this->compileWheres();
            $parts[]  = 'WHERE ' . $whereStr;
            $bindings = array_merge($bindings, $whereBindings);
        }

        return [implode(' ', $parts), $bindings];
    }

    /**
     * Run an aggregate function (COUNT, SUM, MAX, …) and return the raw row. ⚠ OVERRIDE WARNING — ModelQueryBuilder::aggregate() MUST remain in place. This base implementation indexes $results as an array ($results[0]), which works when get() returns a plain array (QueryBuilder). In ModelQueryBuilder, get() returns a Collection, so $results[0] would throw "Cannot use object of type Collection as array". The override in ModelQueryBuilder bypasses hydration and calls connection->select() directly. Do NOT remove ModelQueryBuilder::aggregate().
     *
     * Responsibility: Run an aggregate function (COUNT, SUM, MAX, …) and return the raw row. ⚠ OVERRIDE WARNING — ModelQueryBuilder::aggregate() MUST remain in place. This base implementation indexes $results as an array ($results[0]), which works when get() returns a plain array (QueryBuilder). In ModelQueryBuilder, get() returns a Collection, so $results[0] would throw "Cannot use object of type Collection as array". The override in ModelQueryBuilder bypasses hydration and calls connection->select() directly. Do NOT remove ModelQueryBuilder::aggregate().
     */
    protected function aggregate(string $function, string $column = '*'): ?array
    {
        $this->columns = ["$function($column) as aggregate"];
        $results       = $this->get();
        return $results[0] ?? null;
    }
}
