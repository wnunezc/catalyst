<?php

declare(strict_types=1);

use Catalyst\Framework\Authorization\CanonicalPermissionGrantMigrator;
use Catalyst\Framework\Database\Migration;

/**
 * Migrates persisted grants to canonical Workspaces and Operations permissions.
 *
 * Responsibility: Provisions the rollback journal and delegates reversible tenant-scoped RBAC data migration.
 */
return new class extends Migration {
    public function getVersion(): string
    {
        return '20260612010000';
    }

    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS `canonical_permission_migration_permissions` (
                `migration_key` VARCHAR(100) NOT NULL,
                `tenant_id` INT UNSIGNED NOT NULL,
                `permission_id` INT UNSIGNED NOT NULL,
                `target_slug` VARCHAR(100) NOT NULL,
                PRIMARY KEY (`migration_key`, `tenant_id`, `permission_id`),
                UNIQUE KEY `uq_canonical_permission_migration_slug` (`migration_key`, `tenant_id`, `target_slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->statement(
            'CREATE TABLE IF NOT EXISTS `canonical_permission_migration_grants` (
                `migration_key` VARCHAR(100) NOT NULL,
                `tenant_id` INT UNSIGNED NOT NULL,
                `role_id` INT UNSIGNED NOT NULL,
                `permission_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`migration_key`, `tenant_id`, `role_id`, `permission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        (new CanonicalPermissionGrantMigrator($this->connection()))->migrate();
    }

    public function down(): void
    {
        (new CanonicalPermissionGrantMigrator($this->connection()))->rollback();
        $this->statement('DROP TABLE IF EXISTS `canonical_permission_migration_grants`');
        $this->statement('DROP TABLE IF EXISTS `canonical_permission_migration_permissions`');
    }
};
