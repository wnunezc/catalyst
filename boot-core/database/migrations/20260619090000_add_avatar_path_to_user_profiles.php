<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260619090000';
    }

    public function up(): void
    {
        if (!$this->tableExists('user_profiles') || $this->columnExists('user_profiles', 'avatar_path')) {
            return;
        }

        $this->statement(
            'ALTER TABLE `user_profiles`
                ADD COLUMN `avatar_path` VARCHAR(255) DEFAULT NULL AFTER `department`'
        );
    }

    public function down(): void
    {
        if (!$this->tableExists('user_profiles') || !$this->columnExists('user_profiles', 'avatar_path')) {
            return;
        }

        $this->statement('ALTER TABLE `user_profiles` DROP COLUMN `avatar_path`');
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
