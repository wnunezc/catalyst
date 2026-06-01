<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use PDO;
use Throwable;

final class AdminReadinessProbe
{
    /**
     * @param array<string, mixed> $db
     */
    public function hasActiveAdministrator(array $db): bool
    {
        $host = trim((string) ($db['db_host'] ?? ''));
        $port = (int) ($db['db_port'] ?? 3306);
        $name = trim((string) ($db['db_database'] ?? ''));
        $user = trim((string) ($db['db_username'] ?? ''));
        $pass = (string) ($db['db_password'] ?? '');

        if ($host === '' || $name === '' || $user === '') {
            return false;
        }

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name),
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            $statement = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM users u
                 INNER JOIN user_roles ur ON ur.user_id = u.id
                 INNER JOIN roles r ON r.id = ur.role_id
                 WHERE r.slug = :role AND u.active = :active"
            );

            if ($statement === false) {
                return false;
            }

            $statement->execute([
                'role' => 'admin',
                'active' => 1,
            ]);

            return (int) $statement->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }
}
