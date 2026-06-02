<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Adds retention, attachment, and reporting persistence to the shared runtime.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision archival columns and the tables required for resource attachments and report runs.
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
        return '20260520010000';
    }

    /**
     * Applies the schema changes required for retention, attachments, and report run history.
     *
     * Responsibility: Applies the schema changes required for retention, attachments, and report run history.
     */
    public function up(): void
    {
        $this->ensureMediaRetentionColumns();
        $this->ensureDocumentArtifactRetentionColumns();
        $this->ensureResourceAttachmentsTable();
        $this->ensureReportRunsTable();
    }

    /**
     * Preserves forward-only runtime history when rollback is requested for this migration.
     *
     * Responsibility: Preserves forward-only runtime history when rollback is requested for this migration.
     */
    public function down(): void
    {
        // Forward-only hardening step. Rolling back would drop attachment and
        // reporting history already linked to live runtime records.
    }

    /**
     * Adds media archival columns and indexes used by retention-aware library queries.
     *
     * Responsibility: Adds media archival columns and indexes used by retention-aware library queries.
     */
    private function ensureMediaRetentionColumns(): void
    {
        if (!$this->tableExists('media_library')) {
            return;
        }

        if (!$this->columnExists('media_library', 'archived_at')) {
            $this->statement(
                'ALTER TABLE `media_library`
                 ADD COLUMN `archived_at` DATETIME DEFAULT NULL AFTER `size_bytes`'
            );
        }

        if (!$this->columnExists('media_library', 'archived_by')) {
            $this->statement(
                'ALTER TABLE `media_library`
                 ADD COLUMN `archived_by` INT UNSIGNED DEFAULT NULL AFTER `archived_at`'
            );
        }

        if (!$this->indexExists('media_library', 'idx_media_library_archived_at')) {
            $this->statement(
                'ALTER TABLE `media_library`
                 ADD KEY `idx_media_library_archived_at` (`tenant_id`, `archived_at`, `created_at`)'
            );
        }
    }

    /**
     * Adds document artifact archival columns and indexes used by retention-aware queries.
     *
     * Responsibility: Adds document artifact archival columns and indexes used by retention-aware queries.
     */
    private function ensureDocumentArtifactRetentionColumns(): void
    {
        if (!$this->tableExists('document_artifacts')) {
            return;
        }

        if (!$this->columnExists('document_artifacts', 'archived_at')) {
            $this->statement(
                'ALTER TABLE `document_artifacts`
                 ADD COLUMN `archived_at` DATETIME DEFAULT NULL AFTER `rendered_content`'
            );
        }

        if (!$this->columnExists('document_artifacts', 'archived_by')) {
            $this->statement(
                'ALTER TABLE `document_artifacts`
                 ADD COLUMN `archived_by` INT UNSIGNED DEFAULT NULL AFTER `archived_at`'
            );
        }

        if (!$this->indexExists('document_artifacts', 'idx_document_artifacts_archived_at')) {
            $this->statement(
                'ALTER TABLE `document_artifacts`
                 ADD KEY `idx_document_artifacts_archived_at` (`tenant_id`, `archived_at`, `created_at`)'
            );
        }
    }

    /**
     * Creates the attachment table that connects domain records to media items or document artifacts.
     *
     * Responsibility: Creates the attachment table that connects domain records to media items or document artifacts.
     */
    private function ensureResourceAttachmentsTable(): void
    {
        if ($this->tableExists('resource_attachments')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `resource_attachments` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `resource_key` VARCHAR(120) NOT NULL,
                `record_id` BIGINT UNSIGNED NOT NULL,
                `media_item_id` BIGINT UNSIGNED DEFAULT NULL,
                `document_artifact_id` BIGINT UNSIGNED DEFAULT NULL,
                `purpose` VARCHAR(80) NOT NULL DEFAULT \'attachment\',
                `attachment_type` VARCHAR(80) NOT NULL DEFAULT \'file\',
                `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
                `detached_at` DATETIME DEFAULT NULL,
                `detached_by` INT UNSIGNED DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_resource_attachments_target` (`tenant_id`, `resource_key`, `record_id`, `detached_at`),
                KEY `idx_resource_attachments_media` (`tenant_id`, `media_item_id`, `detached_at`),
                KEY `idx_resource_attachments_artifact` (`tenant_id`, `document_artifact_id`, `detached_at`),
                CONSTRAINT `fk_resource_attachment_media`
                    FOREIGN KEY (`media_item_id`) REFERENCES `media_library` (`id`)
                    ON DELETE CASCADE,
                CONSTRAINT `fk_resource_attachment_artifact`
                    FOREIGN KEY (`document_artifact_id`) REFERENCES `document_artifacts` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates the report run table used to track generated exports and their output artifacts.
     *
     * Responsibility: Creates the report run table used to track generated exports and their output artifacts.
     */
    private function ensureReportRunsTable(): void
    {
        if ($this->tableExists('report_runs')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `report_runs` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `report_key` VARCHAR(150) NOT NULL,
                `format` VARCHAR(20) NOT NULL DEFAULT \'csv\',
                `status` VARCHAR(20) NOT NULL DEFAULT \'pending\',
                `criteria_json` JSON DEFAULT NULL,
                `attach_resource_key` VARCHAR(120) DEFAULT NULL,
                `attach_record_id` BIGINT UNSIGNED DEFAULT NULL,
                `queued_job_id` BIGINT UNSIGNED DEFAULT NULL,
                `output_media_item_id` BIGINT UNSIGNED DEFAULT NULL,
                `output_attachment_id` BIGINT UNSIGNED DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `started_at` DATETIME DEFAULT NULL,
                `completed_at` DATETIME DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_report_runs_status` (`tenant_id`, `status`, `created_at`),
                KEY `idx_report_runs_key` (`tenant_id`, `report_key`, `created_at`),
                KEY `idx_report_runs_output_media` (`output_media_item_id`),
                KEY `idx_report_runs_output_attachment` (`output_attachment_id`),
                CONSTRAINT `fk_report_run_media`
                    FOREIGN KEY (`output_media_item_id`) REFERENCES `media_library` (`id`)
                    ON DELETE SET NULL,
                CONSTRAINT `fk_report_run_attachment`
                    FOREIGN KEY (`output_attachment_id`) REFERENCES `resource_attachments` (`id`)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Checks information_schema so schema changes remain idempotent for an existing column.
     *
     * Responsibility: Checks information_schema so schema changes remain idempotent for an existing column.
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

    /**
     * Checks information_schema so schema changes remain idempotent for an existing index.
     *
     * Responsibility: Checks information_schema so schema changes remain idempotent for an existing index.
     */
    private function indexExists(string $table, string $index): bool
    {
        $row = $this->selectOne(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = :table
               AND index_name = :index
             LIMIT 1',
            [
                ':table' => $table,
                ':index' => $index,
            ]
        );

        return $row !== null;
    }
};
