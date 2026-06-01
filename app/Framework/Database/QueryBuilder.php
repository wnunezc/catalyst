<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database;

use Catalyst\Helpers\Exceptions\QueryException;

/**
 * Fluent SQL query builder
 *
 * Provides a fluent interface for building and executing SQL queries
 * against a database Connection.
 *
 * @package Catalyst\Framework\Database
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

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = SqlReference::assertTable($table);
    }

    // -------------------------------------------------------------------------
    // Column selection
    // -------------------------------------------------------------------------

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

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    public function whereEqual(string $column, mixed $value): self
    {
        return $this->where($column, '=', $value);
    }

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

    public function orWhereIn(string $column, array $values): self
    {
        return $this->whereIn($column, $values, 'OR');
    }

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

    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        return $this->whereNull($column, $boolean, true);
    }

    // -------------------------------------------------------------------------
    // ORDER, GROUP, HAVING, JOIN
    // -------------------------------------------------------------------------

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = [
            'column' => SqlReference::assertColumn($column, 'order column'),
            'direction' => SqlReference::normalizeDirection($direction),
        ];
        return $this;
    }

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

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    // -------------------------------------------------------------------------
    // LIMIT / OFFSET / pagination
    // -------------------------------------------------------------------------

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function forPage(int $page, int $perPage): self
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    // -------------------------------------------------------------------------
    // Execution — SELECT
    // -------------------------------------------------------------------------

    /**
     * Execute and return the first matching row, or null.
     *
     * Return type is `mixed` to allow ModelQueryBuilder to narrow the return
     * type to `?Model` via PHP 8 covariant return types without violating
     * the Liskov Substitution Principle at the PHP engine level.
     * Direct callers of QueryBuilder (not ModelQueryBuilder) always receive
     * `?array` at runtime; the broadened signature is a type-system necessity.
     *
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
     * Execute and return all matching rows.
     *
     * Return type is `mixed` to allow ModelQueryBuilder to narrow the return
     * type to `Collection` via PHP 8 covariant return types.
     * Direct callers of QueryBuilder always receive `array` at runtime.
     *
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
     * Alias for get().
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
     * @throws QueryException
     */
    public function insert(array $values): int
    {
        return $this->connection->insert($this->table, $values);
    }

    /**
     * Update matching rows and return the number of affected rows.
     *
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

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    // -------------------------------------------------------------------------
    // SQL compilation (protected)
    // -------------------------------------------------------------------------

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

    protected function compileColumns(): string
    {
        return implode(', ', array_map(
            static fn (string $column): string => SqlReference::assertSelectable($column),
            $this->columns
        ));
    }

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

    protected function compileOrders(): string
    {
        $parts = [];
        foreach ($this->orders as $order) {
            $parts[] = "{$order['column']} {$order['direction']}";
        }
        return implode(', ', $parts);
    }

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
     * Run an aggregate function (COUNT, SUM, MAX, …) and return the raw row.
     *
     * ⚠ OVERRIDE WARNING — ModelQueryBuilder::aggregate() MUST remain in place.
     * This base implementation indexes $results as an array ($results[0]), which
     * works when get() returns a plain array (QueryBuilder). In ModelQueryBuilder,
     * get() returns a Collection, so $results[0] would throw
     * "Cannot use object of type Collection as array". The override in
     * ModelQueryBuilder bypasses hydration and calls connection->select() directly.
     * Do NOT remove ModelQueryBuilder::aggregate().
     */
    protected function aggregate(string $function, string $column = '*'): ?array
    {
        $this->columns = ["$function($column) as aggregate"];
        $results       = $this->get();
        return $results[0] ?? null;
    }
}
