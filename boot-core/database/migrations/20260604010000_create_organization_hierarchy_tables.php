<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates organization hierarchy storage.
 *
 * Responsibility: Provisions tenant-scoped organizations, units, hierarchy scopes, levels and reusable classifications.
 */
return new class extends Migration {
    /**
     * Returns the migration version identifier.
     *
     * Responsibility: Exposes the timestamp contract consumed by migration discovery and status tooling.
     */
    public function getVersion(): string
    {
        return '20260604010000';
    }

    /**
     * Applies organization hierarchy tables and optional role classification columns.
     *
     * Responsibility: Creates the reusable organization model without changing RBAC authorization semantics.
     */
    public function up(): void
    {
        $this->createOrganizations();
        $this->createOrganizationUnits();
        $this->createHierarchyScopes();
        $this->createHierarchyLevels();
        $this->createOrganizationClassifications();
        $this->createRoleOrganizationUnits();
        $this->extendRoles();
    }

    /**
     * Removes organization hierarchy tables and role classification columns.
     *
     * Responsibility: Reverts this migration while preserving unrelated RBAC data.
     */
    public function down(): void
    {
        if ($this->tableExists('roles')) {
            if ($this->columnExists('roles', 'hierarchy_level_id')) {
                $this->statement('ALTER TABLE `roles` DROP COLUMN `hierarchy_level_id`');
            }
            if ($this->columnExists('roles', 'hierarchy_scope_id')) {
                $this->statement('ALTER TABLE `roles` DROP COLUMN `hierarchy_scope_id`');
            }
        }

        foreach ([
            'role_organization_units',
            'organization_classifications',
            'hierarchy_levels',
            'hierarchy_scopes',
            'organization_units',
            'organizations',
        ] as $table) {
            if ($this->tableExists($table)) {
                $this->statement('DROP TABLE `' . $table . '`');
            }
        }
    }

    /**
     * Creates organization metadata.
     *
     * Responsibility: Stores tenant-local organization identities used by hierarchy scopes and units.
     */
    private function createOrganizations(): void
    {
        if ($this->tableExists('organizations')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `organizations` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `name` VARCHAR(160) NOT NULL,
                `slug` VARCHAR(120) NOT NULL,
                `description` VARCHAR(255) DEFAULT NULL,
                `is_default` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_organizations_tenant_slug` (`tenant_id`, `slug`),
                KEY `idx_organizations_default` (`tenant_id`, `is_default`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates horizontal organization unit storage.
     *
     * Responsibility: Stores departments, areas, teams, services and parent-child unit relationships.
     */
    private function createOrganizationUnits(): void
    {
        if ($this->tableExists('organization_units')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `organization_units` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `organization_id` BIGINT UNSIGNED NOT NULL,
                `parent_id` BIGINT UNSIGNED DEFAULT NULL,
                `name` VARCHAR(160) NOT NULL,
                `code` VARCHAR(80) NOT NULL,
                `unit_type` VARCHAR(80) DEFAULT NULL,
                `description` VARCHAR(255) DEFAULT NULL,
                `visual_token` VARCHAR(80) DEFAULT NULL,
                `color` CHAR(7) DEFAULT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_organization_units_tenant_code` (`tenant_id`, `organization_id`, `code`),
                KEY `idx_organization_units_parent` (`tenant_id`, `parent_id`),
                KEY `idx_organization_units_active` (`tenant_id`, `is_active`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates hierarchy scope storage.
     *
     * Responsibility: Stores independent hierarchy axes such as authority, academic level or operational readiness.
     */
    private function createHierarchyScopes(): void
    {
        if ($this->tableExists('hierarchy_scopes')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `hierarchy_scopes` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `organization_id` BIGINT UNSIGNED NOT NULL,
                `scope_key` VARCHAR(120) NOT NULL,
                `label` VARCHAR(160) NOT NULL,
                `description` VARCHAR(255) DEFAULT NULL,
                `visual_token` VARCHAR(80) DEFAULT NULL,
                `color` CHAR(7) DEFAULT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_hierarchy_scopes_tenant_key` (`tenant_id`, `organization_id`, `scope_key`),
                KEY `idx_hierarchy_scopes_active` (`tenant_id`, `is_active`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates hierarchy level storage.
     *
     * Responsibility: Stores ordered ranks, grades and levels under each hierarchy scope.
     */
    private function createHierarchyLevels(): void
    {
        if ($this->tableExists('hierarchy_levels')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `hierarchy_levels` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `scope_id` BIGINT UNSIGNED NOT NULL,
                `code` VARCHAR(80) NOT NULL,
                `label` VARCHAR(160) NOT NULL,
                `level_order` INT NOT NULL DEFAULT 0,
                `description` VARCHAR(255) DEFAULT NULL,
                `visual_token` VARCHAR(80) DEFAULT NULL,
                `color` CHAR(7) DEFAULT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_hierarchy_levels_scope_code` (`tenant_id`, `scope_id`, `code`),
                KEY `idx_hierarchy_levels_order` (`tenant_id`, `scope_id`, `level_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates generic classification assignments.
     *
     * Responsibility: Links users, roles, courses, certifications and future records to configured hierarchy metadata.
     */
    private function createOrganizationClassifications(): void
    {
        if ($this->tableExists('organization_classifications')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `organization_classifications` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `resource_key` VARCHAR(120) NOT NULL,
                `record_id` VARCHAR(120) NOT NULL,
                `organization_id` BIGINT UNSIGNED NOT NULL,
                `scope_id` BIGINT UNSIGNED NOT NULL,
                `level_id` BIGINT UNSIGNED NOT NULL,
                `unit_id` BIGINT UNSIGNED DEFAULT NULL,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_org_classifications_resource_scope_unit` (`tenant_id`, `resource_key`, `record_id`, `scope_id`, `unit_id`),
                KEY `idx_org_classifications_target` (`tenant_id`, `resource_key`, `record_id`),
                KEY `idx_org_classifications_level` (`tenant_id`, `level_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates role-to-unit links.
     *
     * Responsibility: Allows roles to be associated with one or more horizontal units without changing permissions.
     */
    private function createRoleOrganizationUnits(): void
    {
        if ($this->tableExists('role_organization_units')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `role_organization_units` (
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `role_id` INT UNSIGNED NOT NULL,
                `unit_id` BIGINT UNSIGNED NOT NULL,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`tenant_id`, `role_id`, `unit_id`),
                KEY `idx_role_org_units_unit` (`tenant_id`, `unit_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Adds optional hierarchy columns to roles when the RBAC table exists.
     *
     * Responsibility: Stores role classification metadata while leaving role and permission checks unchanged.
     */
    private function extendRoles(): void
    {
        if (!$this->tableExists('roles')) {
            return;
        }

        if (!$this->columnExists('roles', 'hierarchy_scope_id')) {
            $this->statement('ALTER TABLE `roles` ADD COLUMN `hierarchy_scope_id` BIGINT UNSIGNED DEFAULT NULL AFTER `description`');
        }

        if (!$this->columnExists('roles', 'hierarchy_level_id')) {
            $this->statement('ALTER TABLE `roles` ADD COLUMN `hierarchy_level_id` BIGINT UNSIGNED DEFAULT NULL AFTER `hierarchy_scope_id`');
        }
    }

    /**
     * Determines whether a column exists on a table.
     *
     * Responsibility: Supports idempotent optional schema extension during reusable-base upgrades.
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
