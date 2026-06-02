<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates the table that records deployment runs.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove deployment execution history persistence.
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
        return '20260519151000';
    }

    /**
     * Creates the deployment runs table when it is absent.
     *
     * Responsibility: Creates the deployment runs table when it is absent.
     */
    public function up(): void
    {
        if ($this->tableExists('deployment_runs')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `deployment_runs` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `profile_key` VARCHAR(120) NOT NULL,
                `release_id` VARCHAR(160) NOT NULL,
                `environment` VARCHAR(30) NOT NULL,
                `status` VARCHAR(30) NOT NULL,
                `dry_run` TINYINT(1) NOT NULL DEFAULT 0,
                `artifact_path` VARCHAR(255) DEFAULT NULL,
                `remote_path` VARCHAR(255) DEFAULT NULL,
                `summary_json` JSON DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `started_at` DATETIME DEFAULT NULL,
                `finished_at` DATETIME DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_deployment_runs_release_id` (`release_id`),
                KEY `idx_deployment_runs_profile` (`profile_key`),
                KEY `idx_deployment_runs_status` (`status`),
                KEY `idx_deployment_runs_started_at` (`started_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Removes the deployment runs table when it exists.
     *
     * Responsibility: Removes the deployment runs table when it exists.
     */
    public function down(): void
    {
        if ($this->tableExists('deployment_runs')) {
            $this->statement('DROP TABLE `deployment_runs`');
        }
    }
};
