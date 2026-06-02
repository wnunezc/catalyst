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

use RuntimeException;

/**
 * Base class for database migration definitions.
 *
 * @package Catalyst\Framework\Database
 * Responsibility: Provides migration versioning, connection access, SQL execution, table checks, and foreign key helpers.
 */
abstract class Migration
{
    private ?Connection $connection = null;

    /**
     * Returns the migration version identifier.
     *
     * Responsibility: Returns the migration version identifier.
     */
    abstract public function getVersion(): string;

    /**
     * Applies the schema or data changes for the migration.
     *
     * Responsibility: Applies the schema or data changes for the migration.
     */
    abstract public function up(): void;

    /**
     * Reverts the schema or data changes for the migration.
     *
     * Responsibility: Reverts the schema or data changes for the migration.
     */
    abstract public function down(): void;

    /**
     * Sets the database connection used by this migration.
     *
     * Responsibility: Sets the database connection used by this migration.
     */
    final public function setConnection(Connection $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Returns the active database connection for this migration.
     *
     * Responsibility: Returns the active database connection for this migration.
     */
    protected function connection(): Connection
    {
        if ($this->connection === null) {
            throw new RuntimeException('Migration connection has not been initialised.');
        }

        return $this->connection;
    }

    /**
     * Executes a SQL statement against the migration connection.
     *
     * Responsibility: Executes a SQL statement against the migration connection.
     */
    protected function statement(string $sql): void
    {
        $this->connection()->getPdo()->exec($sql);
    }

    /**
     * Executes a prepared SQL statement and returns affected rows.
     *
     * Responsibility: Executes a prepared SQL statement and returns affected rows.
     */
    protected function execute(string $sql, array $params = []): int
    {
        return $this->connection()->execute($sql, $params);
    }

    /**
     * Executes a SQL select query and returns all rows.
     *
     * Responsibility: Executes a SQL select query and returns all rows.
     */
    protected function select(string $sql, array $params = []): array
    {
        return $this->connection()->select($sql, $params);
    }

    /**
     * Executes a SQL select query and returns the first row.
     *
     * Responsibility: Executes a SQL select query and returns the first row.
     */
    protected function selectOne(string $sql, array $params = []): ?array
    {
        return $this->connection()->selectOne($sql, $params);
    }

    /**
     * Determines whether a table exists in the current database.
     *
     * Responsibility: Determines whether a table exists in the current database.
     */
    protected function tableExists(string $table): bool
    {
        $row = $this->selectOne(
            'SELECT 1
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table
             LIMIT 1',
            [':table' => $table]
        );

        return $row !== null;
    }

    /**
     * Determines whether a named foreign key exists on a table.
     *
     * Responsibility: Determines whether a named foreign key exists on a table.
     */
    protected function foreignKeyExists(string $table, string $constraint): bool
    {
        $row = $this->selectOne(
            'SELECT 1
             FROM information_schema.table_constraints
             WHERE constraint_schema = DATABASE()
               AND table_name = :table
               AND constraint_name = :constraint
               AND constraint_type = :type
             LIMIT 1',
            [
                ':table'      => $table,
                ':constraint' => $constraint,
                ':type'       => 'FOREIGN KEY',
            ]
        );

        return $row !== null;
    }

    /**
     * Resolves the delete rule configured for a named foreign key.
     *
     * Responsibility: Resolves the delete rule configured for a named foreign key.
     */
    protected function foreignKeyDeleteRule(string $table, string $constraint): ?string
    {
        $row = $this->selectOne(
            'SELECT rc.DELETE_RULE AS delete_rule
             FROM information_schema.referential_constraints rc
             INNER JOIN information_schema.table_constraints tc
                 ON tc.constraint_schema = rc.constraint_schema
                AND tc.constraint_name = rc.constraint_name
                AND tc.table_name = rc.table_name
             WHERE rc.constraint_schema = DATABASE()
               AND tc.table_name = :table
               AND tc.constraint_name = :constraint
             LIMIT 1',
            [
                ':table'      => $table,
                ':constraint' => $constraint,
            ]
        );

        return $row !== null ? strtoupper((string) $row['delete_rule']) : null;
    }

    /**
     * Drops a foreign key when it exists on the target table.
     *
     * Responsibility: Drops a foreign key when it exists on the target table.
     */
    protected function dropForeignKey(string $table, string $constraint): void
    {
        $sql = sprintf(
            'ALTER TABLE %s DROP FOREIGN KEY %s',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($constraint)
        );

        $this->statement($sql);
    }

    /**
     * Adds a foreign key constraint to a table.
     *
     * Responsibility: Adds a foreign key constraint to a table.
     */
    protected function addForeignKey(
        string $table,
        string $constraint,
        string $column,
        string $referenceTable,
        string $referenceColumn = 'id',
        string $onDelete = 'CASCADE',
        string $onUpdate = 'RESTRICT'
    ): void {
        $sql = sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($constraint),
            $this->quoteIdentifier($column),
            $this->quoteIdentifier($referenceTable),
            $this->quoteIdentifier($referenceColumn),
            strtoupper($onDelete),
            strtoupper($onUpdate)
        );

        $this->statement($sql);
    }

    /**
     * Quotes a SQL identifier for safe DDL composition.
     *
     * Responsibility: Quotes a SQL identifier for safe DDL composition.
     */
    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
