<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates framework scoped sequence counters.
 *
 * Responsibility: Provision tenant-aware transactional sequence storage for framework and app modules.
 */
return new class extends Migration {
    /**
     * Returns the migration version identifier.
     *
     * Responsibility: Exposes the timestamp contract consumed by migration discovery and status tooling.
     */
    public function getVersion(): string
    {
        return '20260603010000';
    }

    /**
     * Creates the sequence counter table.
     *
     * Responsibility: Defines the database structure required by transactional sequence storage.
     */
    public function up(): void
    {
        if ($this->tableExists('framework_sequences')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `framework_sequences` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
                `scope_key` VARCHAR(190) NOT NULL,
                `sequence_key` VARCHAR(190) NOT NULL DEFAULT \'default\',
                `current_value` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_framework_sequences_scope` (`tenant_id`, `scope_key`, `sequence_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Drops the sequence counter table.
     *
     * Responsibility: Removes the framework-owned sequence storage table during rollback.
     */
    public function down(): void
    {
        if ($this->tableExists('framework_sequences')) {
            $this->statement('DROP TABLE `framework_sequences`');
        }
    }
};
