<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Adds an account-level guard against TOTP code replay.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Persist the last accepted TOTP timestep so the same MFA code cannot be reused.
 */
return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260615010000';
    }

    public function up(): void
    {
        if (!$this->tableExists('users') || $this->columnExists('users', 'mfa_last_totp_step')) {
            return;
        }

        $this->statement(
            'ALTER TABLE `users`
             ADD COLUMN `mfa_last_totp_step` BIGINT UNSIGNED DEFAULT NULL AFTER `mfa_backup_codes`'
        );
    }

    public function down(): void
    {
        if (!$this->tableExists('users') || !$this->columnExists('users', 'mfa_last_totp_step')) {
            return;
        }

        $this->statement('ALTER TABLE `users` DROP COLUMN `mfa_last_totp_step`');
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
