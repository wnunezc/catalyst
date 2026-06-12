<?php

declare(strict_types=1);

namespace Catalyst\Repository\Configuration\Services;

use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Log\Logger;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/**
 * Provisions the initial administrator required by environment setup.
 *
 * @package Catalyst\Repository\Configuration\Services
 * Responsibility: Detects active administrators, ensures the admin role and creates the first privileged account.
 */
final class SetupAdminProvisioner
{
    /**
     * Initializes the Setup Admin Provisioner instance.
     *
     * Responsibility: Initializes the Setup Admin Provisioner instance.
     */
public function __construct(
        private readonly Logger $logger,
        private readonly ConfigManager $config
    ) {
    }

    /**
     * Creates a provisioner with the framework logger.
     */
public static function make(): self
    {
        return new self(Logger::getInstance(), ConfigManager::getInstance());
    }

    /**
     * Determines whether an administrator exists.
     *
     * Responsibility: Determines whether an administrator exists.
     */
public function adminExists(PDO $pdo): bool
    {
        try {
            $stmt = $pdo->query(
                "SELECT COUNT(*) FROM users u
                 INNER JOIN user_roles ur ON ur.user_id = u.id
                 INNER JOIN roles r ON r.id = ur.role_id
                 WHERE r.slug = 'admin' AND u.active = 1"
            );

            return $stmt !== false && (int) $stmt->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Determines whether a user exists for the email address.
     *
     * Responsibility: Determines whether a user exists for the email address.
     */
public function userExistsByEmail(PDO $pdo, string $email): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);

            return $stmt->fetchColumn() !== false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Ensures the administrator role exists.
     *
     * Responsibility: Ensures the administrator role exists.
     */
public function ensureAdminRole(PDO $pdo): int
    {
        try {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug = 'admin' LIMIT 1");
            $stmt->execute();
            $roleId = $stmt->fetchColumn();

            if ($roleId !== false) {
                return (int) $roleId;
            }

            $pdo->prepare(
                "INSERT INTO roles (tenant_id, name, slug, description)
                 VALUES (1, 'Administrator', 'admin', 'System administrator')"
            )->execute();

            return (int) $pdo->lastInsertId();
        } catch (Throwable $e) {
            $this->logger->error('SetupAdminProvisioner: ensureAdminRole failed', [
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Unable to ensure admin role: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Creates the administrator account and role assignment atomically.
     *
     * Responsibility: Creates the administrator account.
     */
public function createAdmin(PDO $pdo, string $name, string $email, string $password): void
    {
        $startedTransaction = !$pdo->inTransaction();

        try {
            if ($startedTransaction) {
                $pdo->beginTransaction();
            }

            $roleId = $this->ensureAdminRole($pdo);
            $stmt = $pdo->prepare(
                'INSERT INTO users (tenant_id, name, email, password, active, email_verified)
                 VALUES (:tenant_id, :name, :email, :password, 1, 1)'
            );
            $stmt->execute([
                ':tenant_id' => 1,
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->bcryptCost()]),
            ]);
            $userId = (int) $pdo->lastInsertId();

            $assignment = $pdo->prepare(
                'INSERT INTO user_roles (user_id, role_id, tenant_id)
                 VALUES (:user_id, :role_id, :tenant_id)'
            );
            $assignment->execute([
                ':user_id' => $userId,
                ':role_id' => $roleId,
                ':tenant_id' => 1,
            ]);

            if ($startedTransaction) {
                $pdo->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $this->logger->error('SetupAdminProvisioner: admin creation failed', [
                'error' => $e->getMessage(),
            ]);

            $message = $e instanceof PDOException && (string) $e->getCode() === '23000'
                ? 'An account already exists for the supplied email.'
                : $e->getMessage();

            throw new RuntimeException($message, 0, $e);
        }
    }

    private function bcryptCost(): int
    {
        try {
            $rounds = (int) $this->config->get('security.security.bcrypt_rounds');

            return max(10, min(16, $rounds > 0 ? $rounds : 12));
        } catch (Throwable) {
            return 12;
        }
    }
}
