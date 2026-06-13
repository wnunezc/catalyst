<?php

declare(strict_types=1);

namespace CatalystTest\Authorization;

use Catalyst\Framework\Authorization\AccountRecoveryPermissionMigrator;
use Catalyst\Framework\Database\Connection;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;
use PDO;
use RuntimeException;

final class AccountRecoveryPermissionMigratorTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/requirement-loader/error-catcher.php';
    }

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
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            'CREATE TABLE permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                slug TEXT NOT NULL,
                description TEXT,
                UNIQUE (tenant_id, slug)
            )'
        );
        $pdo->exec(
            'CREATE TABLE role_permissions (
                role_id INTEGER NOT NULL,
                permission_id INTEGER NOT NULL,
                tenant_id INTEGER NOT NULL,
                PRIMARY KEY (role_id, permission_id)
            )'
        );
        $pdo->exec(
            'CREATE TABLE account_recovery_permission_migrations (
                tenant_id INTEGER NOT NULL,
                permission_id INTEGER NOT NULL,
                PRIMARY KEY (tenant_id, permission_id)
            )'
        );

        return new class ($pdo) extends Connection {
            public function __construct(PDO $pdo)
            {
                parent::__construct('', 0, '', '', '', 'account-recovery-permission-test');
                $this->pdo = $pdo;
            }
        };
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
