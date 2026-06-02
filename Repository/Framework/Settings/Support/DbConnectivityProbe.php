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
use PDOException;
use Throwable;

/**
 * Defines the Db Connectivity Probe class contract.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Coordinates the db connectivity probe behavior within its module boundary.
 */
final class DbConnectivityProbe
{
    /**
     * Handles the probe workflow.
     */
    public function probe(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password
    ): string {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);
            new PDO($dsn, $username, $password, $options);

            return 'ok';
        } catch (PDOException $exception) {
            if ((int) $exception->getCode() === 1049 || str_contains($exception->getMessage(), 'Unknown database')) {
                try {
                    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
                    new PDO($dsn, $username, $password, $options);

                    return 'db_missing';
                } catch (Throwable) {
                    return 'unreachable';
                }
            }

            return 'unreachable';
        } catch (Throwable) {
            return 'unreachable';
        }
    }
}
