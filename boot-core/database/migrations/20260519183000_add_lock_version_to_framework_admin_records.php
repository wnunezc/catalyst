<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $tables = [
        'document_templates',
        'automation_rules',
        'media_library',
        'metadata_field_definitions',
    ];

    public function getVersion(): string
    {
        return '20260519183000';
    }

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!$this->tableExists($table) || $this->columnExists($table, 'lock_version')) {
                continue;
            }

            $this->statement(sprintf(
                'ALTER TABLE %s ADD COLUMN `lock_version` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `updated_by`',
                $this->quoteIdentifier($table)
            ));
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!$this->tableExists($table) || !$this->columnExists($table, 'lock_version')) {
                continue;
            }

            $this->statement(sprintf(
                'ALTER TABLE %s DROP COLUMN `lock_version`',
                $this->quoteIdentifier($table)
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
