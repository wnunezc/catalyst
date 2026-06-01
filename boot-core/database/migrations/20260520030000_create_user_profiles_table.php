<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260520030000';
    }

    public function up(): void
    {
        if ($this->tableExists('user_profiles')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `user_profiles` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `user_id` INT UNSIGNED NOT NULL,
                `document_id` VARCHAR(80) DEFAULT NULL,
                `phone` VARCHAR(40) DEFAULT NULL,
                `organization` VARCHAR(160) DEFAULT NULL,
                `position` VARCHAR(120) DEFAULT NULL,
                `department` VARCHAR(120) DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                `lock_version` INT UNSIGNED NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_user_profiles_tenant_user` (`tenant_id`, `user_id`),
                KEY `idx_user_profiles_tenant_document` (`tenant_id`, `document_id`),
                KEY `idx_user_profiles_tenant_organization` (`tenant_id`, `organization`),
                CONSTRAINT `fk_user_profiles_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        if ($this->tableExists('user_profiles')) {
            $this->statement('DROP TABLE `user_profiles`');
        }
    }
};
