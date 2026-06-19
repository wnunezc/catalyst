<?php

declare(strict_types=1);

namespace CatalystTest\Integration\Authorization;

use Catalyst\Framework\Authorization\CanonicalPermissionGrantMigrator;
use Catalyst\Framework\Database\Connection;
use Catalyst\Repository\Operations\Support\OperationsAccessContract;
use Catalyst\Repository\Workspaces\Support\WorkspacesAccessContract;
use CatalystTest\Integration\Support\MySqlIntegrationTestCase;
use CatalystTest\Support\Assert;
use Throwable;

final class CanonicalPermissionGrantMigratorMysqlTest extends MySqlIntegrationTestCase
{
    public function testMigratesEquivalentGrantsIdempotentlyAndRollsBack(): void
    {
        $connection = $this->database();
        $this->seedLegacyPermissions($connection);
        $migrator = new CanonicalPermissionGrantMigrator($connection);

        $migrator->migrate();
        $migrator->migrate();

        Assert::same(11, $this->canonicalPermissionCount($connection));
        Assert::same(
            [
                'manage-catalogs',
                'manage-operations-deployments',
                'manage-operations-tenancy',
                'manage-platform-operations',
                'manage-workspaces-catalogs',
                'manage-workspaces-localization',
                'manage-workspaces-module-designer',
            ],
            $this->rolePermissionSlugs($connection, 2)
        );
        Assert::same([], $this->rolePermissionSlugs($connection, 3));
        Assert::true($this->allDefinitionsFallbackToAdmin());

        $migrator->rollback();

        Assert::same(0, $this->canonicalPermissionCount($connection));
        Assert::same(
            ['manage-catalogs', 'manage-platform-operations'],
            $this->rolePermissionSlugs($connection, 2)
        );
    }

    public function testRollbackPreservesPreexistingCanonicalPermissionAndGrant(): void
    {
        $connection = $this->database();
        $this->seedLegacyPermissions($connection);
        $permissionId = $this->insertPermission(
            $connection,
            1,
            'Canonical Catalog Access',
            'manage-workspaces-catalogs'
        );
        $connection->execute(
            'INSERT INTO role_permissions (role_id, permission_id, tenant_id) VALUES (?, ?, ?)',
            [3, $permissionId, 1]
        );

        $migrator = new CanonicalPermissionGrantMigrator($connection);
        $migrator->migrate();
        $migrator->rollback();

        Assert::same(1, (int) $connection->selectOne(
            'SELECT COUNT(*) AS aggregate FROM permissions WHERE id = ?',
            [$permissionId]
        )['aggregate']);
        Assert::same(['manage-workspaces-catalogs'], $this->rolePermissionSlugs($connection, 3));
    }

    public function testMigrationRollsBackAllWritesWhenACanonicalPermissionCannotBeCreated(): void
    {
        $connection = $this->database();
        $this->seedLegacyPermissions($connection);
        $this->insertPermission(
            $connection,
            1,
            CanonicalPermissionGrantMigrator::permissionName('manage-workspaces-media-fields'),
            'conflicting-permission'
        );

        try {
            (new CanonicalPermissionGrantMigrator($connection))->migrate();
        } catch (Throwable) {
            Assert::same(0, $this->canonicalPermissionCount($connection));
            Assert::same(0, (int) $connection->selectOne(
                'SELECT COUNT(*) AS aggregate FROM canonical_permission_migration_permissions'
            )['aggregate']);
            Assert::same(0, (int) $connection->selectOne(
                'SELECT COUNT(*) AS aggregate FROM canonical_permission_migration_grants'
            )['aggregate']);

            return;
        }

        Assert::true(false, 'Expected the permission migration to fail atomically.');
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY permissions_tenant_name_unique (tenant_id, name),
                UNIQUE KEY permissions_tenant_slug_unique (tenant_id, slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $pdo->exec(
            'CREATE TABLE roles (
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
                tenant_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (role_id, permission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $pdo->exec(
            'CREATE TABLE canonical_permission_migration_permissions (
                migration_key VARCHAR(191) NOT NULL,
                tenant_id BIGINT UNSIGNED NOT NULL,
                permission_id BIGINT UNSIGNED NOT NULL,
                target_slug VARCHAR(191) NOT NULL,
                PRIMARY KEY (migration_key, tenant_id, permission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $pdo->exec(
            'CREATE TABLE canonical_permission_migration_grants (
                migration_key VARCHAR(191) NOT NULL,
                tenant_id BIGINT UNSIGNED NOT NULL,
                role_id BIGINT UNSIGNED NOT NULL,
                permission_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (migration_key, tenant_id, role_id, permission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        return $this->connection();
    }

    private function seedLegacyPermissions(Connection $connection): void
    {
        $connection->execute(
            'INSERT INTO roles (id, tenant_id, name, slug) VALUES
                (1, 1, ?, ?),
                (2, 1, ?, ?),
                (3, 1, ?, ?)',
            ['Administrator', 'admin', 'Editor', 'editor', 'Denied', 'denied']
        );
        $catalogId = $this->insertPermission($connection, 1, 'Manage Catalogs', 'manage-catalogs');
        $platformId = $this->insertPermission(
            $connection,
            1,
            'Manage Platform Operations',
            'manage-platform-operations'
        );
        $connection->execute(
            'INSERT INTO role_permissions (role_id, permission_id, tenant_id) VALUES
                (?, ?, ?),
                (?, ?, ?)',
            [2, $catalogId, 1, 2, $platformId, 1]
        );
    }

    private function insertPermission(
        Connection $connection,
        int $tenantId,
        string $name,
        string $slug
    ): int {
        return $connection->insert('permissions', [
            'tenant_id' => $tenantId,
            'name' => $name,
            'slug' => $slug,
            'description' => null,
        ]);
    }

    private function canonicalPermissionCount(Connection $connection): int
    {
        $placeholders = implode(', ', array_fill(0, count(CanonicalPermissionGrantMigrator::targetSlugs()), '?'));
        $row = $connection->selectOne(
            'SELECT COUNT(*) AS aggregate FROM permissions WHERE slug IN (' . $placeholders . ')',
            CanonicalPermissionGrantMigrator::targetSlugs()
        );

        return (int) ($row['aggregate'] ?? 0);
    }

    /**
     * @return string[]
     */
    private function rolePermissionSlugs(Connection $connection, int $roleId): array
    {
        $rows = $connection->select(
            'SELECT p.slug
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?
             ORDER BY p.slug',
            [$roleId]
        );

        return array_column($rows, 'slug');
    }

    private function allDefinitionsFallbackToAdmin(): bool
    {
        $workspaces = require dirname(__DIR__, 4) . '/Repository/Framework/Workspaces/module.php';
        $operations = require dirname(__DIR__, 4) . '/Repository/Framework/Operations/module.php';
        $definitions = array_merge(
            (array) ($workspaces['permissions'] ?? []),
            (array) ($operations['permissions'] ?? [])
        );

        if (count($definitions) !== count(WorkspacesAccessContract::permissions()) + count(OperationsAccessContract::permissions())) {
            return false;
        }

        foreach ($definitions as $definition) {
            if (($definition['role_fallback_any'] ?? null) !== ['admin']) {
                return false;
            }
        }

        return WorkspacesAccessContract::permissions() === array_column(
            (array) ($workspaces['permissions'] ?? []),
            'slug'
        ) && OperationsAccessContract::permissions() === array_column(
            (array) ($operations['permissions'] ?? []),
            'slug'
        );
    }
}
