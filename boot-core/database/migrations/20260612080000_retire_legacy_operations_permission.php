<?php

declare(strict_types=1);

use Catalyst\Framework\Authorization\LegacyOperationsPermissionRetirer;
use Catalyst\Framework\Database\Migration;

return new class extends Migration {
    public function getVersion(): string
    {
        return '20260612080000';
    }

    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS `retired_operations_permissions` (
                `migration_key` VARCHAR(100) NOT NULL,
                `tenant_id` INT UNSIGNED NOT NULL,
                `permission_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL,
                `description` VARCHAR(255) NULL,
                `created_at` TIMESTAMP NULL,
                PRIMARY KEY (`migration_key`, `tenant_id`, `permission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->statement(
            'CREATE TABLE IF NOT EXISTS `retired_operations_permission_grants` (
                `migration_key` VARCHAR(100) NOT NULL,
                `tenant_id` INT UNSIGNED NOT NULL,
                `role_id` INT UNSIGNED NOT NULL,
                `permission_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`migration_key`, `tenant_id`, `role_id`, `permission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        (new LegacyOperationsPermissionRetirer($this->connection()))->retire();
    }

    public function down(): void
    {
        (new LegacyOperationsPermissionRetirer($this->connection()))->rollback();
        $this->statement('DROP TABLE IF EXISTS `retired_operations_permission_grants`');
        $this->statement('DROP TABLE IF EXISTS `retired_operations_permissions`');
    }
};
