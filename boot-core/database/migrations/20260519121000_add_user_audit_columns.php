<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Adds audit actor columns to user records.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove user creation and update actor references.
 */
return new class extends Migration
{
    /**
     * Returns the timestamp identifier used by the migration runner to order and track this migration.
     *
     * Responsibility: Returns the timestamp identifier used by the migration runner to order and track this migration.
     */
    public function getVersion(): string
    {
        return '20260519121000';
    }

    /**
     * Adds missing audit actor columns to the users table.
     *
     * Responsibility: Adds missing audit actor columns to the users table.
     */
    public function up(): void
    {
        if (!$this->tableExists('users')) {
            return;
        }

        foreach ([
            'created_by' => 'INT UNSIGNED NULL DEFAULT NULL AFTER `updated_at`',
            'updated_by' => 'INT UNSIGNED NULL DEFAULT NULL AFTER `created_by`',
        ] as $column => $definition) {
            if ($this->columnExists('users', $column)) {
                continue;
            }

            $this->statement(sprintf(
                'ALTER TABLE `users` ADD COLUMN `%s` %s',
                $column,
                $definition
            ));
        }
    }

    /**
     * Removes audit actor columns from the users table.
     *
     * Responsibility: Removes audit actor columns from the users table.
     */
    public function down(): void
    {
        if (!$this->tableExists('users')) {
            return;
        }

        foreach (['updated_by', 'created_by'] as $column) {
            if (!$this->columnExists('users', $column)) {
                continue;
            }

            $this->statement(sprintf(
                'ALTER TABLE `users` DROP COLUMN `%s`',
                $column
            ));
        }
    }

    /**
     * Checks information_schema so schema changes remain idempotent for an existing column.
     *
     * Responsibility: Checks information_schema so schema changes remain idempotent for an existing column.
     */
    private function columnExists(string $table, string $column): bool
    {
        $row = $this->selectOne(
            'SELECT 1
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = :table
               AND column_name = :column
             LIMIT 1',
            [
                ':table' => $table,
                ':column' => $column,
            ]
        );

        return $row !== null;
    }
};
