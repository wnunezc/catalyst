<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Repository\Settings\Support;

use PDO;
use Throwable;

/**
 * Defines the Admin Readiness Probe class contract.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Coordinates the admin readiness probe behavior within its module boundary.
 */
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
