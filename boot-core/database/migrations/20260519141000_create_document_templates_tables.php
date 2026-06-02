<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates the tables that persist document templates and rendered artifacts.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove document template configuration and generated artifact persistence.
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
        return '20260519141000';
    }

    /**
     * Creates document template and artifact tables when absent.
     *
     * Responsibility: Creates document template and artifact tables when absent.
     */
    public function up(): void
    {
        if (!$this->tableExists('document_templates')) {
            $this->statement(
                'CREATE TABLE `document_templates` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(150) NOT NULL,
                    `slug` VARCHAR(150) NOT NULL,
                    `description` TEXT DEFAULT NULL,
                    `format` VARCHAR(20) NOT NULL DEFAULT \'html\',
                    `variables_schema_json` JSON DEFAULT NULL,
                    `sample_payload_json` JSON DEFAULT NULL,
                    `body_template` LONGTEXT NOT NULL,
                    `created_at` DATETIME DEFAULT NULL,
                    `updated_at` DATETIME DEFAULT NULL,
                    `created_by` INT UNSIGNED DEFAULT NULL,
                    `updated_by` INT UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_document_template_slug` (`slug`),
                    KEY `idx_document_templates_name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
        }

        if ($this->tableExists('document_artifacts')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `document_artifacts` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `document_template_id` BIGINT UNSIGNED NOT NULL,
                `workflow_instance_id` BIGINT UNSIGNED DEFAULT NULL,
                `name` VARCHAR(180) NOT NULL,
                `format` VARCHAR(20) NOT NULL DEFAULT \'html\',
                `disk` VARCHAR(30) NOT NULL DEFAULT \'local\',
                `path` VARCHAR(255) NOT NULL,
                `public_url` VARCHAR(255) NOT NULL,
                `checksum_sha256` CHAR(64) NOT NULL,
                `payload_snapshot_json` JSON DEFAULT NULL,
                `rendered_content` LONGTEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_document_artifacts_template` (`document_template_id`),
                KEY `idx_document_artifacts_workflow` (`workflow_instance_id`),
                KEY `idx_document_artifacts_created_at` (`created_at`),
                CONSTRAINT `fk_document_artifact_template`
                    FOREIGN KEY (`document_template_id`) REFERENCES `document_templates` (`id`)
                    ON DELETE CASCADE,
                CONSTRAINT `fk_document_artifact_workflow`
                    FOREIGN KEY (`workflow_instance_id`) REFERENCES `workflow_instances` (`id`)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Removes document artifact and template tables in dependency-safe order.
     *
     * Responsibility: Removes document artifact and template tables in dependency-safe order.
     */
    public function down(): void
    {
        if ($this->tableExists('document_artifacts')) {
            $this->statement('DROP TABLE `document_artifacts`');
        }

        if ($this->tableExists('document_templates')) {
            $this->statement('DROP TABLE `document_templates`');
        }
    }
};
