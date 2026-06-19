<?php

declare(strict_types=1);

namespace CatalystTest\Integration\Authorization;

use Catalyst\Framework\Authorization\AccountRecoveryPermissionMigrator;
use Catalyst\Framework\Database\Connection;
use CatalystTest\Integration\Support\MySqlIntegrationTestCase;
use CatalystTest\Support\Assert;
use RuntimeException;

final class AccountRecoveryPermissionMigratorMysqlTest extends MySqlIntegrationTestCase
{
    public function testCreatesMissingPermissionWithoutGrantingItAndRollsBack(): void
    {
        $connection = $this->database();
        $connection->execute(
            'INSERT INTO permissions (tenant_id, name, slug, description) VALUES (?, ?, ?, ?)',
            [1, 'Manage Users', 'manage-users', 'Existing permission']
        );
        $migrator = new AccountRecoveryPermissionMigrator($connection);

        $migrator->migrate();
        $migrator->migrate();

        Assert::same(1, $this->permissionCount($connection));
        Assert::same(0, $this->grantCount($connection));

        $migrator->rollback();

        Assert::same(0, $this->permissionCount($connection));
    }

    public function testPreservesPreexistingPermissionAndGrantAcrossRollback(): void
    {
        $connection = $this->database();
        $permissionId = $connection->insert('permissions', [
            'tenant_id' => 1,
            'name' => 'Manage Account Recovery',
            'slug' => 'manage-account-recovery',
            'description' => 'Existing permission',
        ]);
        $connection->execute(
            'INSERT INTO role_permissions (role_id, permission_id, tenant_id) VALUES (?, ?, ?)',
            [2, $permissionId, 1]
        );
        $migrator = new AccountRecoveryPermissionMigrator($connection);

        $migrator->migrate();
        $migrator->rollback();

        Assert::same(1, $this->permissionCount($connection));
        Assert::same(1, $this->grantCount($connection));
    }

    public function testRollbackRefusesToDeletePermissionWithANewGrant(): void
    {
        $connection = $this->database();
        $connection->execute(
            'INSERT INTO permissions (tenant_id, name, slug, description) VALUES (?, ?, ?, ?)',
            [1, 'Manage Users', 'manage-users', 'Existing permission']
        );
        $migrator = new AccountRecoveryPermissionMigrator($connection);
        $migrator->migrate();
        $permission = $connection->selectOne(
            'SELECT id FROM permissions WHERE tenant_id = ? AND slug = ?',
            [1, 'manage-account-recovery']
        );
        $connection->execute(
            'INSERT INTO role_permissions (role_id, permission_id, tenant_id) VALUES (?, ?, ?)',
            [2, (int) $permission['id'], 1]
        );

        try {
            $migrator->rollback();
        } catch (RuntimeException) {
            Assert::same(1, $this->permissionCount($connection));
            Assert::same(1, $this->grantCount($connection));

            return;
        }

        Assert::true(false, 'Expected rollback to preserve a permission with a later grant.');
    }

    private function database(): Connection
    {
        $pdo = $this->pdo();
        $pdo->exec(
            'CREATE TABLE permissions (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(191) NOT NULL,
                slug VARCHAR(191) NOT NULL,
                description VARCHAR(255) NULL,
                UNIQUE KEY permissions_tenant_slug_unique (tenant_id, slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $pdo->exec(
            'CREATE TABLE role_permissions (
                role_id BIGINT UNSIGNED NOT NULL,
                permission_id BIGINT UNSIGNED NOT NULL,
                tenant_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (role_id, permission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $pdo->exec(
            'CREATE TABLE account_recovery_permission_migrations (
                tenant_id BIGINT UNSIGNED NOT NULL,
                permission_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (tenant_id, permission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        return $this->connection();
    }

    private function permissionCount(Connection $connection): int
    {
        return (int) $connection->selectOne(
            'SELECT COUNT(*) AS aggregate FROM permissions WHERE slug = ?',
            ['manage-account-recovery']
        )['aggregate'];
    }

    private function grantCount(Connection $connection): int
    {
        return (int) $connection->selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM role_permissions rp
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE p.slug = ?',
            ['manage-account-recovery']
        )['aggregate'];
    }
}
