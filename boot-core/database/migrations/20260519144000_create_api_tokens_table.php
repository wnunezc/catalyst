<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519144000';
    }

    public function up(): void
    {
        if ($this->tableExists('api_tokens')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `api_tokens` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(150) NOT NULL,
                `token_prefix` VARCHAR(24) NOT NULL,
                `token_hash` CHAR(64) NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `abilities_json` JSON DEFAULT NULL,
                `last_used_at` DATETIME DEFAULT NULL,
                `expires_at` DATETIME DEFAULT NULL,
                `revoked_at` DATETIME DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_api_token_hash` (`token_hash`),
                KEY `idx_api_tokens_user` (`user_id`),
                KEY `idx_api_tokens_prefix` (`token_prefix`),
                KEY `idx_api_tokens_revoked_at` (`revoked_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        if (!$this->tableExists('api_tokens')) {
            return;
        }

        $this->statement('DROP TABLE `api_tokens`');
    }
};
