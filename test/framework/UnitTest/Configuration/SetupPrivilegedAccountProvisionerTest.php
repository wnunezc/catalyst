<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Log\Logger;
use Catalyst\Repository\Configuration\Services\SetupPrivilegedAccountProvisioner;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;
use PDO;
use RuntimeException;

final class SetupPrivilegedAccountProvisionerTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/constant/sys-constant.php';
    }

    public function testCreatesActiveVerifiedPrivilegedAccountAndRoleAssignmentAtomically(): void
    {
        $pdo = $this->database(true);
        $service = new SetupPrivilegedAccountProvisioner(Logger::getInstance(), ConfigManager::getInstance());

        $service->createPrivilegedAccount($pdo, 'Initial Privileged Account', 'privileged@example.com', 'correct-password');

        $user = $pdo->query('SELECT * FROM users')->fetch(PDO::FETCH_ASSOC);
        $assignment = $pdo->query('SELECT * FROM user_roles')->fetch(PDO::FETCH_ASSOC);
        Assert::same('privileged@example.com', $user['email'] ?? null);
        Assert::same(1, (int) ($user['active'] ?? 0));
        Assert::same(1, (int) ($user['email_verified'] ?? 0));
        Assert::true(password_verify('correct-password', (string) ($user['password'] ?? '')));
        Assert::same((int) ($user['id'] ?? 0), (int) ($assignment['user_id'] ?? 0));
    }

    public function testRollsBackUserAndRoleWhenAssignmentFails(): void
    {
        $pdo = $this->database(false);
        $service = new SetupPrivilegedAccountProvisioner(Logger::getInstance(), ConfigManager::getInstance());

        try {
            $service->createPrivilegedAccount($pdo, 'Initial Privileged Account', 'privileged@example.com', 'correct-password');
        } catch (RuntimeException) {
            Assert::same(0, (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn());
            Assert::same(0, (int) $pdo->query('SELECT COUNT(*) FROM roles')->fetchColumn());

            return;
        }

            Assert::true(false, 'Expected role assignment failure to roll back privileged account creation.');
    }

    private function database(bool $withAssignments): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                active INTEGER NOT NULL,
                email_verified INTEGER NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                description TEXT
            )'
        );

        if ($withAssignments) {
            $pdo->exec(
                'CREATE TABLE user_roles (
                    user_id INTEGER NOT NULL,
                    role_id INTEGER NOT NULL,
                    tenant_id INTEGER NOT NULL,
                    PRIMARY KEY (user_id, role_id)
                )'
            );
        }

        return $pdo;
    }
}
