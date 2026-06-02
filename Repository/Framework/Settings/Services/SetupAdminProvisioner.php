<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Services;

use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Helpers\Log\Logger;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Provisions the initial administrator required by environment setup.
 *
 * @package Catalyst\Repository\Settings\Services
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
        private readonly Logger $logger
    ) {
    }

    /**
     * Creates a provisioner with the framework logger.
     */
public static function make(): self
    {
        return new self(Logger::getInstance());
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
public function ensureAdminRole(PDO $pdo): void
    {
        try {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug = 'admin' LIMIT 1");
            $stmt->execute();

            if ($stmt->fetchColumn() !== false) {
                return;
            }

            $pdo->prepare(
                "INSERT INTO roles (name, slug, description) VALUES ('Administrator', 'admin', 'System administrator')"
            )->execute();
        } catch (Throwable $e) {
            $this->logger->error('SetupAdminProvisioner: ensureAdminRole failed', [
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Unable to ensure admin role: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Creates the administrator account.
     *
     * Responsibility: Creates the administrator account.
     */
public function createAdmin(string $name, string $email, string $password): void
    {
        try {
            UserProvider::getInstance()->create(
                $name,
                $email,
                $password,
                'admin',
                true
            );
        } catch (Throwable $e) {
            $this->logger->error('SetupAdminProvisioner: admin creation failed', [
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }
}
