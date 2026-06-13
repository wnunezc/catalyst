<?php

declare(strict_types=1);

use Catalyst\Framework\Authorization\AccountRecoveryPermissionMigrator;
use Catalyst\Framework\Database\Migration;

return new class extends Migration {
    public function getVersion(): string
    {
        return '20260613010000';
    }

    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS `account_recovery_permission_migrations` (
                `tenant_id` INT UNSIGNED NOT NULL,
                `permission_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`tenant_id`, `permission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        (new AccountRecoveryPermissionMigrator($this->connection()))->migrate();
    }

    public function down(): void
    {
        (new AccountRecoveryPermissionMigrator($this->connection()))->rollback();
        $this->statement('DROP TABLE IF EXISTS `account_recovery_permission_migrations`');
    }
};
