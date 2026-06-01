<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519132000';
    }

    public function up(): void
    {
        if ($this->tableExists('metadata_field_values')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `metadata_field_values` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `resource_key` VARCHAR(100) NOT NULL,
                `record_id` BIGINT UNSIGNED NOT NULL,
                `field_definition_id` BIGINT UNSIGNED NOT NULL,
                `value_text` LONGTEXT DEFAULT NULL,
                `value_number` DECIMAL(18,4) DEFAULT NULL,
                `value_boolean` TINYINT(1) DEFAULT NULL,
                `value_date` DATE DEFAULT NULL,
                `value_datetime` DATETIME DEFAULT NULL,
                `media_item_id` BIGINT UNSIGNED DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_metadata_field_record` (`resource_key`, `record_id`, `field_definition_id`),
                KEY `idx_metadata_value_resource_record` (`resource_key`, `record_id`),
                KEY `idx_metadata_value_field_definition` (`field_definition_id`),
                KEY `idx_metadata_value_media_item` (`media_item_id`),
                CONSTRAINT `fk_metadata_values_definition`
                    FOREIGN KEY (`field_definition_id`) REFERENCES `metadata_field_definitions` (`id`)
                    ON DELETE CASCADE,
                CONSTRAINT `fk_metadata_values_media_item`
                    FOREIGN KEY (`media_item_id`) REFERENCES `media_library` (`id`)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        if (!$this->tableExists('metadata_field_values')) {
            return;
        }

        $this->statement('DROP TABLE `metadata_field_values`');
    }
};
