<?php

declare(strict_types=1);

namespace CatalystTest\Integration\Configuration;

use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Log\Logger;
use Catalyst\Repository\Configuration\Services\SetupPrivilegedAccountProvisioner;
use CatalystTest\Integration\Support\MySqlIntegrationTestCase;
use CatalystTest\Support\Assert;
use PDO;
use RuntimeException;

final class SetupPrivilegedAccountProvisionerMysqlTest extends MySqlIntegrationTestCase
{
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
        $pdo = $this->pdo();
        $pdo->exec(
            'CREATE TABLE users (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(191) NOT NULL,
                email VARCHAR(191) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                active TINYINT(1) NOT NULL,
                email_verified TINYINT(1) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $pdo->exec(
            'CREATE TABLE roles (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(191) NOT NULL,
                slug VARCHAR(191) NOT NULL UNIQUE,
                description VARCHAR(255) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        if ($withAssignments) {
            $pdo->exec(
                'CREATE TABLE user_roles (
                    user_id BIGINT UNSIGNED NOT NULL,
                    role_id BIGINT UNSIGNED NOT NULL,
                    tenant_id BIGINT UNSIGNED NOT NULL,
                    PRIMARY KEY (user_id, role_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
        }

        return $pdo;
    }
}
