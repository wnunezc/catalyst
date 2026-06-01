<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519121000';
    }

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
