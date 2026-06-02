<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates the table that coordinates temporary record claims.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove persistence for record claim ownership and release history.
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
        return '20260519170000';
    }

    /**
     * Creates the record claims table when it is absent.
     *
     * Responsibility: Creates the record claims table when it is absent.
     */
    public function up(): void
    {
        if ($this->tableExists('record_claims')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `record_claims` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `resource_key` VARCHAR(160) NOT NULL,
                `record_id` BIGINT UNSIGNED NOT NULL,
                `claim_token` CHAR(32) NOT NULL,
                `claimed_by` BIGINT UNSIGNED DEFAULT NULL,
                `claimed_by_label` VARCHAR(120) DEFAULT NULL,
                `claimed_at` DATETIME DEFAULT NULL,
                `expires_at` DATETIME DEFAULT NULL,
                `released_at` DATETIME DEFAULT NULL,
                `release_reason` VARCHAR(255) DEFAULT NULL,
                `metadata` JSON DEFAULT NULL,
                `lock_version` INT UNSIGNED NOT NULL DEFAULT 1,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_record_claim_resource` (`resource_key`, `record_id`),
                KEY `idx_record_claim_claimed_by` (`claimed_by`),
                KEY `idx_record_claim_expires_at` (`expires_at`),
                KEY `idx_record_claim_released_at` (`released_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Removes the record claims table when it exists.
     *
     * Responsibility: Removes the record claims table when it exists.
     */
    public function down(): void
    {
        if ($this->tableExists('record_claims')) {
            $this->statement('DROP TABLE `record_claims`');
        }
    }
};
