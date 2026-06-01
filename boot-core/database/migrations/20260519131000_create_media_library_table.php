<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519131000';
    }

    public function up(): void
    {
        if ($this->tableExists('media_library')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `media_library` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(150) NOT NULL,
                `original_name` VARCHAR(255) NOT NULL,
                `disk` VARCHAR(30) NOT NULL DEFAULT \'local\',
                `path` VARCHAR(255) NOT NULL,
                `public_url` VARCHAR(255) NOT NULL,
                `mime_type` VARCHAR(150) NOT NULL,
                `extension` VARCHAR(20) DEFAULT NULL,
                `size_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_media_library_name` (`name`),
                KEY `idx_media_library_disk` (`disk`),
                KEY `idx_media_library_mime` (`mime_type`),
                KEY `idx_media_library_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        if (!$this->tableExists('media_library')) {
            return;
        }

        $this->statement('DROP TABLE `media_library`');
    }
};
