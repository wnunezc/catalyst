<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates the table that records workflow transitions.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove workflow state transition history persistence.
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
        return '20260519140100';
    }

    /**
     * Creates the workflow transitions table when it is absent.
     *
     * Responsibility: Creates the workflow transitions table when it is absent.
     */
    public function up(): void
    {
        if ($this->tableExists('workflow_transitions')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `workflow_transitions` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `workflow_instance_id` BIGINT UNSIGNED NOT NULL,
                `transition_key` VARCHAR(120) NOT NULL,
                `from_state` VARCHAR(80) NOT NULL,
                `to_state` VARCHAR(80) NOT NULL,
                `notes` TEXT DEFAULT NULL,
                `metadata` JSON DEFAULT NULL,
                `actor_id` INT UNSIGNED DEFAULT NULL,
                `occurred_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_workflow_transitions_instance` (`workflow_instance_id`),
                KEY `idx_workflow_transitions_occurred_at` (`occurred_at`),
                CONSTRAINT `fk_workflow_transition_instance`
                    FOREIGN KEY (`workflow_instance_id`) REFERENCES `workflow_instances` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Removes the workflow transitions table when it exists.
     *
     * Responsibility: Removes the workflow transitions table when it exists.
     */
    public function down(): void
    {
        if (!$this->tableExists('workflow_transitions')) {
            return;
        }

        $this->statement('DROP TABLE `workflow_transitions`');
    }
};
