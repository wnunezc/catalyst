<?php

declare(strict_types=1);

namespace CatalystTest\Integration\Authorization;

use Catalyst\Framework\Authorization\ApiManagementPermissionMigrator;
use CatalystTest\Integration\Support\MySqlIntegrationTestCase;
use CatalystTest\Support\Assert;

final class ApiManagementPermissionMigratorMysqlTest extends MySqlIntegrationTestCase
{
    public function testRenamesPermissionWithoutChangingPersistedGrantAndRollsBack(): void
    {
        $connection = $this->connection();
        $this->createSchema();
        $connection->execute(
            "INSERT INTO permissions VALUES (7, 1, 'Manage Operations API Platform', 'manage-operations-api-platform')"
        );
        $connection->execute('INSERT INTO role_permissions VALUES (3, 7, 1)');

        $migrator = new ApiManagementPermissionMigrator($connection);

        $migrator->migrate();
        $migrator->migrate();

        Assert::same(
            'manage-operations-api-management',
            $connection->selectOne('SELECT slug FROM permissions WHERE id = 7')['slug']
        );
        Assert::same(1, (int) $connection->selectOne(
            'SELECT COUNT(*) AS aggregate FROM role_permissions WHERE role_id = 3 AND permission_id = 7'
        )['aggregate']);

        $migrator->rollback();

        Assert::same(
            'manage-operations-api-platform',
            $connection->selectOne('SELECT slug FROM permissions WHERE id = 7')['slug']
        );
    }

    private function createSchema(): void
    {
        $pdo = $this->pdo();
        $pdo->exec(
            'CREATE TABLE permissions (
                id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                tenant_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(191) NOT NULL,
                slug VARCHAR(191) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $pdo->exec(
            'CREATE TABLE role_permissions (
                role_id BIGINT UNSIGNED NOT NULL,
                permission_id BIGINT UNSIGNED NOT NULL,
                tenant_id BIGINT UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $pdo->exec(
            'CREATE TABLE api_management_permission_migrations (
                tenant_id BIGINT UNSIGNED NOT NULL,
                permission_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (tenant_id, permission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}
