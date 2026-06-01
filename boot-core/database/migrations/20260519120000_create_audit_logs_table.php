<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519120000';
    }

    public function up(): void
    {
        if ($this->tableExists('audit_logs')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `audit_logs` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `channel` VARCHAR(50) NOT NULL,
                `event_name` VARCHAR(150) DEFAULT NULL,
                `action` VARCHAR(50) NOT NULL,
                `resource` VARCHAR(100) NOT NULL,
                `resource_id` VARCHAR(50) DEFAULT NULL,
                `resource_label` VARCHAR(255) DEFAULT NULL,
                `actor_id` INT UNSIGNED DEFAULT NULL,
                `actor_type` VARCHAR(50) DEFAULT NULL,
                `request_method` VARCHAR(10) DEFAULT NULL,
                `request_uri` VARCHAR(255) DEFAULT NULL,
                `ip_address` VARCHAR(64) DEFAULT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `before_payload` JSON DEFAULT NULL,
                `after_payload` JSON DEFAULT NULL,
                `metadata` JSON DEFAULT NULL,
                `occurred_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_audit_logs_occurred_at` (`occurred_at`),
                INDEX `idx_audit_logs_resource` (`resource`),
                INDEX `idx_audit_logs_action` (`action`),
                INDEX `idx_audit_logs_channel` (`channel`),
                INDEX `idx_audit_logs_actor_id` (`actor_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        if (!$this->tableExists('audit_logs')) {
            return;
        }

        $this->statement('DROP TABLE `audit_logs`');
    }
};
