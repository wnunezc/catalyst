<?php

declare(strict_types=1);

namespace CatalystTest\Authorization;

use Catalyst\Framework\Authorization\ApiManagementPermissionMigrator;
use Catalyst\Framework\Database\Connection;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;
use PDO;

final class ApiManagementPermissionMigratorTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/requirement-loader/error-catcher.php';
    }

    public function testRenamesPermissionWithoutChangingPersistedGrantAndRollsBack(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE permissions (id INTEGER PRIMARY KEY, tenant_id INTEGER, name TEXT, slug TEXT)');
        $pdo->exec('CREATE TABLE role_permissions (role_id INTEGER, permission_id INTEGER, tenant_id INTEGER)');
        $pdo->exec('CREATE TABLE api_management_permission_migrations (tenant_id INTEGER, permission_id INTEGER, PRIMARY KEY (tenant_id, permission_id))');
        $pdo->exec("INSERT INTO permissions VALUES (7, 1, 'Manage Operations API Platform', 'manage-operations-api-platform')");
        $pdo->exec('INSERT INTO role_permissions VALUES (3, 7, 1)');

        $connection = new class ($pdo) extends Connection {
            public function __construct(PDO $pdo)
            {
                parent::__construct('', 0, '', '', '', 'api-management-permission-test');
                $this->pdo = $pdo;
            }
        };
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
}
