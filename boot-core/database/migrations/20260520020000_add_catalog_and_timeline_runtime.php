<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Adds catalog and timeline persistence to the shared runtime.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision catalog definitions, catalog items, timeline events, and catalog-backed metadata fields.
 */
return new class extends Migration
{
    /**
     * Returns the migration version identifier.
     *
     * Responsibility: Returns the migration version identifier.
     */
    public function getVersion(): string
    {
        return '20260520020000';
    }

    /**
     * Creates catalog and timeline structures and extends metadata definitions.
     *
     * Responsibility: Creates catalog and timeline structures and extends metadata definitions.
     */
    public function up(): void
    {
        $this->createCatalogDefinitions();
        $this->createCatalogItems();
        $this->createTimelineEvents();
        $this->extendMetadataDefinitions();
    }

    /**
     * Removes catalog and timeline structures and the metadata catalog reference.
     *
     * Responsibility: Removes catalog and timeline structures and the metadata catalog reference.
     */
    public function down(): void
    {
        if ($this->tableExists('timeline_events')) {
            $this->statement('DROP TABLE `timeline_events`');
        }

        if ($this->tableExists('catalog_items')) {
            $this->statement('DROP TABLE `catalog_items`');
        }

        if ($this->tableExists('catalog_definitions')) {
            $this->statement('DROP TABLE `catalog_definitions`');
        }

        if ($this->tableExists('metadata_field_definitions') && $this->columnExists('metadata_field_definitions', 'catalog_key')) {
            $this->statement('ALTER TABLE `metadata_field_definitions` DROP COLUMN `catalog_key`');
        }
    }

    /**
     * Creates the table that describes tenant-scoped catalogs.
     *
     * Responsibility: Creates the table that describes tenant-scoped catalogs.
     */
    private function createCatalogDefinitions(): void
    {
        if ($this->tableExists('catalog_definitions')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `catalog_definitions` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `catalog_key` VARCHAR(120) NOT NULL,
                `label` VARCHAR(150) NOT NULL,
                `description` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                `lock_version` INT UNSIGNED NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_catalog_definitions_tenant_key` (`tenant_id`, `catalog_key`),
                KEY `idx_catalog_definitions_tenant_label` (`tenant_id`, `label`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates the table that stores entries for each catalog.
     *
     * Responsibility: Creates the table that stores entries for each catalog.
     */
    private function createCatalogItems(): void
    {
        if ($this->tableExists('catalog_items')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `catalog_items` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `catalog_definition_id` BIGINT UNSIGNED NOT NULL,
                `item_key` VARCHAR(120) NOT NULL,
                `label` VARCHAR(150) NOT NULL,
                `description` TEXT DEFAULT NULL,
                `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                `valid_from` DATETIME DEFAULT NULL,
                `valid_to` DATETIME DEFAULT NULL,
                `sort_order` INT UNSIGNED NOT NULL DEFAULT 100,
                `metadata_json` JSON DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                `lock_version` INT UNSIGNED NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_catalog_items_tenant_key` (`tenant_id`, `catalog_definition_id`, `item_key`),
                KEY `idx_catalog_items_tenant_catalog` (`tenant_id`, `catalog_definition_id`),
                KEY `idx_catalog_items_tenant_validity` (`tenant_id`, `is_enabled`, `valid_from`, `valid_to`),
                CONSTRAINT `fk_catalog_items_definition`
                    FOREIGN KEY (`catalog_definition_id`) REFERENCES `catalog_definitions` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates the table that records resource timeline events.
     *
     * Responsibility: Creates the table that records resource timeline events.
     */
    private function createTimelineEvents(): void
    {
        if ($this->tableExists('timeline_events')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `timeline_events` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `resource_key` VARCHAR(120) NOT NULL,
                `record_id` BIGINT UNSIGNED NOT NULL,
                `event_key` VARCHAR(120) NOT NULL,
                `event_type` VARCHAR(30) NOT NULL,
                `label` VARCHAR(150) NOT NULL,
                `metadata_json` JSON DEFAULT NULL,
                `occurred_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_timeline_events_tenant_resource` (`tenant_id`, `resource_key`, `record_id`),
                KEY `idx_timeline_events_tenant_type` (`tenant_id`, `event_type`, `occurred_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Adds a catalog reference to metadata field definitions.
     *
     * Responsibility: Adds a catalog reference to metadata field definitions.
     */
    private function extendMetadataDefinitions(): void
    {
        if (!$this->tableExists('metadata_field_definitions') || $this->columnExists('metadata_field_definitions', 'catalog_key')) {
            return;
        }

        $this->statement(
            'ALTER TABLE `metadata_field_definitions`
             ADD COLUMN `catalog_key` VARCHAR(120) DEFAULT NULL AFTER `options_json`'
        );
        $this->statement(
            'ALTER TABLE `metadata_field_definitions`
             ADD KEY `idx_metadata_field_catalog_key` (`catalog_key`)'
        );
    }

    /**
     * Determines whether a column exists in a table.
     *
     * Responsibility: Determines whether a column exists in a table.
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
