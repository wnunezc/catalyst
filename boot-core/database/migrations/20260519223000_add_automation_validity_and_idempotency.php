<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Adds temporal automation controls and idempotency persistence.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision automation validity windows and durable idempotency key storage.
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
        return '20260519223000';
    }

    /**
     * Adds automation validity columns and the idempotency keys table.
     *
     * Responsibility: Adds automation validity columns and the idempotency keys table.
     */
    public function up(): void
    {
        $this->ensureAutomationValidityColumns();
        $this->ensureIdempotencyTable();
    }

    /**
     * Preserves forward-only runtime history when rollback is requested for this migration.
     *
     * Responsibility: Preserves forward-only runtime history when rollback is requested for this migration.
     */
    public function down(): void
    {
        // Forward-only hardening step. Rolling back would drop runtime
        // dedup history and temporal metadata already attached to live rules.
    }

    /**
     * Adds validity window columns and their lookup index to automation rules.
     *
     * Responsibility: Adds validity window columns and their lookup index to automation rules.
     */
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

    /**
     * Creates the table that records idempotency keys and their outcomes.
     *
     * Responsibility: Creates the table that records idempotency keys and their outcomes.
     */
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

    /**
     * Checks information_schema so schema changes remain idempotent for an existing index.
     *
     * Responsibility: Checks information_schema so schema changes remain idempotent for an existing index.
     */
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
