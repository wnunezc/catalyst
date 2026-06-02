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
 * Defines the Migration class contract.
 *
 * @package Catalyst\Framework\Database
 * Responsibility: Coordinates the migration behavior within its module boundary.
 */
abstract class Migration
{
    private ?Connection $connection = null;

    /**
     * Returns the version value.
     */
    abstract public function getVersion(): string;

    /**
     * Handles the up workflow.
     */
    abstract public function up(): void;

    /**
     * Handles the down workflow.
     */
    abstract public function down(): void;

    /**
     * Updates the connection value.
     */
    final public function setConnection(Connection $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Handles the connection workflow.
     */
    protected function connection(): Connection
    {
        if ($this->connection === null) {
            throw new RuntimeException('Migration connection has not been initialised.');
        }

        return $this->connection;
    }

    /**
     * Handles the statement workflow.
     */
    protected function statement(string $sql): void
    {
        $this->connection()->getPdo()->exec($sql);
    }

    /**
     * Executes the service workflow.
     */
    protected function execute(string $sql, array $params = []): int
    {
        return $this->connection()->execute($sql, $params);
    }

    /**
     * Handles the select workflow.
     */
    protected function select(string $sql, array $params = []): array
    {
        return $this->connection()->select($sql, $params);
    }

    /**
     * Handles the select one workflow.
     */
    protected function selectOne(string $sql, array $params = []): ?array
    {
        return $this->connection()->selectOne($sql, $params);
    }

    /**
     * Handles the table exists workflow.
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
     * Handles the foreign key exists workflow.
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
     * Handles the foreign key delete rule workflow.
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
     * Handles the drop foreign key workflow.
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
     * Handles the add foreign key workflow.
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
     * Handles the quote identifier workflow.
     */
    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
