<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates the table that stores feature flag overrides.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove subject-specific feature flag override persistence.
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
        return '20260519150000';
    }

    /**
     * Creates the feature flag overrides table when it is absent.
     *
     * Responsibility: Creates the feature flag overrides table when it is absent.
     */
    public function up(): void
    {
        if ($this->tableExists('feature_flag_overrides')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `feature_flag_overrides` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `flag_key` VARCHAR(180) NOT NULL,
                `subject_type` VARCHAR(30) NOT NULL,
                `subject_key` VARCHAR(180) NOT NULL,
                `enabled` TINYINT(1) NOT NULL DEFAULT 1,
                `note` VARCHAR(255) DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_feature_flag_override_subject` (`flag_key`, `subject_type`, `subject_key`),
                KEY `idx_feature_flag_override_flag` (`flag_key`),
                KEY `idx_feature_flag_override_subject_type` (`subject_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Removes the feature flag overrides table when it exists.
     *
     * Responsibility: Removes the feature flag overrides table when it exists.
     */
    public function down(): void
    {
        if ($this->tableExists('feature_flag_overrides')) {
            $this->statement('DROP TABLE `feature_flag_overrides`');
        }
    }
};
