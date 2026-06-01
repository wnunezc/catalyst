<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519223000';
    }

    public function up(): void
    {
        $this->ensureAutomationValidityColumns();
        $this->ensureIdempotencyTable();
    }

    public function down(): void
    {
        // Forward-only hardening step. Rolling back would drop runtime
        // dedup history and temporal metadata already attached to live rules.
    }

    private function ensureAutomationValidityColumns(): void
    {
        if (!$this->tableExists('automation_rules')) {
            return;
        }

        if (!$this->columnExists('automation_rules', 'valid_from')) {
            $this->statement(
                'ALTER TABLE `automation_rules`
                 ADD COLUMN `valid_from` DATETIME DEFAULT NULL AFTER `is_enabled`'
            );
        }

        if (!$this->columnExists('automation_rules', 'valid_to')) {
            $this->statement(
                'ALTER TABLE `automation_rules`
                 ADD COLUMN `valid_to` DATETIME DEFAULT NULL AFTER `valid_from`'
            );
        }

        if (!$this->indexExists('automation_rules', 'idx_automation_validity_window')) {
            $this->statement(
                'ALTER TABLE `automation_rules`
                 ADD KEY `idx_automation_validity_window` (`tenant_id`, `is_enabled`, `valid_from`, `valid_to`)'
            );
        }
    }

    private function ensureIdempotencyTable(): void
    {
        if ($this->tableExists('idempotency_keys')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `idempotency_keys` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `scope_key` VARCHAR(190) NOT NULL,
                `idempotency_key` VARCHAR(190) NOT NULL,
                `fingerprint_hash` CHAR(64) NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT \'pending\',
                `outcome_json` JSON DEFAULT NULL,
                `completed_at` DATETIME DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_idempotency_tenant_scope_key` (`tenant_id`, `scope_key`, `idempotency_key`),
                KEY `idx_idempotency_tenant_scope_status` (`tenant_id`, `scope_key`, `status`),
                KEY `idx_idempotency_completed_at` (`completed_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
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

    private function indexExists(string $table, string $index): bool
    {
        $row = $this->selectOne(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = :table
               AND index_name = :index
             LIMIT 1',
            [
                ':table' => $table,
                ':index' => $index,
            ]
        );

        return $row !== null;
    }
};
