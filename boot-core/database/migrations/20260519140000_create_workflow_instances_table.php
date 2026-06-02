<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates the table that stores workflow instances.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove workflow state persistence for runtime resources.
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
        return '20260519140000';
    }

    /**
     * Creates the workflow instances table when it is absent.
     *
     * Responsibility: Creates the workflow instances table when it is absent.
     */
    public function up(): void
    {
        if ($this->tableExists('workflow_instances')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `workflow_instances` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `definition_key` VARCHAR(120) NOT NULL,
                `resource_key` VARCHAR(120) NOT NULL,
                `record_id` BIGINT UNSIGNED NOT NULL,
                `current_state` VARCHAR(80) NOT NULL,
                `context_json` JSON DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_workflow_record` (`definition_key`, `resource_key`, `record_id`),
                KEY `idx_workflow_resource` (`resource_key`, `record_id`),
                KEY `idx_workflow_state` (`current_state`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Removes the workflow instances table when it exists.
     *
     * Responsibility: Removes the workflow instances table when it exists.
     */
    public function down(): void
    {
        if (!$this->tableExists('workflow_instances')) {
            return;
        }

        $this->statement('DROP TABLE `workflow_instances`');
    }
};
