<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database;

use RuntimeException;

abstract class Migration
{
    private ?Connection $connection = null;

    abstract public function getVersion(): string;

    abstract public function up(): void;

    abstract public function down(): void;

    final public function setConnection(Connection $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    protected function connection(): Connection
    {
        if ($this->connection === null) {
            throw new RuntimeException('Migration connection has not been initialised.');
        }

        return $this->connection;
    }

    protected function statement(string $sql): void
    {
        $this->connection()->getPdo()->exec($sql);
    }

    protected function execute(string $sql, array $params = []): int
    {
        return $this->connection()->execute($sql, $params);
    }

    protected function select(string $sql, array $params = []): array
    {
        return $this->connection()->select($sql, $params);
    }

    protected function selectOne(string $sql, array $params = []): ?array
    {
        return $this->connection()->selectOne($sql, $params);
    }

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

    protected function dropForeignKey(string $table, string $constraint): void
    {
        $sql = sprintf(
            'ALTER TABLE %s DROP FOREIGN KEY %s',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($constraint)
        );

        $this->statement($sql);
    }

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

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
