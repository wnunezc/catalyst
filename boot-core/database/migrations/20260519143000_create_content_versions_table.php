<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates the table that stores content versions.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove resource snapshot and change history persistence.
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
        return '20260519143000';
    }

    /**
     * Creates the content versions table when it is absent.
     *
     * Responsibility: Creates the content versions table when it is absent.
     */
    public function up(): void
    {
        if ($this->tableExists('content_versions')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `content_versions` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `resource_key` VARCHAR(120) NOT NULL,
                `record_id` BIGINT UNSIGNED NOT NULL,
                `version_number` INT UNSIGNED NOT NULL,
                `summary` VARCHAR(255) DEFAULT NULL,
                `snapshot_json` JSON NOT NULL,
                `diff_json` JSON DEFAULT NULL,
                `actor_id` INT UNSIGNED DEFAULT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_content_version` (`resource_key`, `record_id`, `version_number`),
                KEY `idx_content_versions_resource` (`resource_key`, `record_id`),
                KEY `idx_content_versions_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Removes the content versions table when it exists.
     *
     * Responsibility: Removes the content versions table when it exists.
     */
    public function down(): void
    {
        if (!$this->tableExists('content_versions')) {
            return;
        }

        $this->statement('DROP TABLE `content_versions`');
    }
};
