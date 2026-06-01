<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519130000';
    }

    public function up(): void
    {
        if ($this->tableExists('metadata_field_definitions')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `metadata_field_definitions` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `resource_key` VARCHAR(100) NOT NULL,
                `field_key` VARCHAR(100) NOT NULL,
                `label` VARCHAR(120) NOT NULL,
                `type` VARCHAR(30) NOT NULL,
                `section_key` VARCHAR(100) DEFAULT NULL,
                `help_text` VARCHAR(255) DEFAULT NULL,
                `placeholder` VARCHAR(255) DEFAULT NULL,
                `default_value` VARCHAR(255) DEFAULT NULL,
                `options_json` JSON DEFAULT NULL,
                `rules_extra` VARCHAR(255) DEFAULT NULL,
                `is_required` TINYINT(1) NOT NULL DEFAULT 0,
                `is_filterable` TINYINT(1) NOT NULL DEFAULT 0,
                `is_listed` TINYINT(1) NOT NULL DEFAULT 0,
                `sort_order` INT UNSIGNED NOT NULL DEFAULT 100,
                `max_length` INT UNSIGNED DEFAULT NULL,
                `min_value` DECIMAL(18,4) DEFAULT NULL,
                `max_value` DECIMAL(18,4) DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_metadata_field_resource_key` (`resource_key`, `field_key`),
                KEY `idx_metadata_field_resource` (`resource_key`),
                KEY `idx_metadata_field_type` (`type`),
                KEY `idx_metadata_field_listed` (`is_listed`),
                KEY `idx_metadata_field_filterable` (`is_filterable`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        if (!$this->tableExists('metadata_field_definitions')) {
            return;
        }

        $this->statement('DROP TABLE `metadata_field_definitions`');
    }
};
