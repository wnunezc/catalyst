<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519142000';
    }

    public function up(): void
    {
        if (!$this->tableExists('automation_rules')) {
            $this->statement(
                'CREATE TABLE `automation_rules` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(150) NOT NULL,
                    `slug` VARCHAR(150) NOT NULL,
                    `description` TEXT DEFAULT NULL,
                    `trigger_type` VARCHAR(20) NOT NULL,
                    `event_name` VARCHAR(180) DEFAULT NULL,
                    `cron_expression` VARCHAR(40) DEFAULT NULL,
                    `condition_json` JSON DEFAULT NULL,
                    `action_type` VARCHAR(40) NOT NULL,
                    `action_payload_json` JSON DEFAULT NULL,
                    `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
                    `last_run_at` DATETIME DEFAULT NULL,
                    `created_at` DATETIME DEFAULT NULL,
                    `updated_at` DATETIME DEFAULT NULL,
                    `created_by` INT UNSIGNED DEFAULT NULL,
                    `updated_by` INT UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_automation_rule_slug` (`slug`),
                    KEY `idx_automation_trigger` (`trigger_type`, `event_name`),
                    KEY `idx_automation_enabled` (`is_enabled`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
        }

        if ($this->tableExists('automation_execution_logs')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `automation_execution_logs` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `rule_id` BIGINT UNSIGNED NOT NULL,
                `trigger_source` VARCHAR(30) NOT NULL,
                `event_name` VARCHAR(180) DEFAULT NULL,
                `status` VARCHAR(30) NOT NULL,
                `message` TEXT DEFAULT NULL,
                `context_json` JSON DEFAULT NULL,
                `result_json` JSON DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_automation_logs_rule` (`rule_id`),
                KEY `idx_automation_logs_status` (`status`),
                KEY `idx_automation_logs_created_at` (`created_at`),
                CONSTRAINT `fk_automation_log_rule`
                    FOREIGN KEY (`rule_id`) REFERENCES `automation_rules` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        if ($this->tableExists('automation_execution_logs')) {
            $this->statement('DROP TABLE `automation_execution_logs`');
        }

        if ($this->tableExists('automation_rules')) {
            $this->statement('DROP TABLE `automation_rules`');
        }
    }
};
