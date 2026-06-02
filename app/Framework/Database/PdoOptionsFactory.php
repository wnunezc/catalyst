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

namespace Catalyst\Framework\Database;

use PDO;

/**
 * Builds safe PDO options for Catalyst database connections.
 *
 * This class avoids referencing driver-specific PDO constants directly
 * when the related extension is not loaded. That makes CLI inspections,
 * smoke checks and partial environments more tolerant while still using
 * MySQL-specific options when pdo_mysql is available.
 */
final class PdoOptionsFactory
{
    /**
     * Default PDO options used by Catalyst.
     *
     * @return array<int, mixed>
     */
    public static function mysql(): array
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        /*
         * PDO::MYSQL_ATTR_INIT_COMMAND only exists when pdo_mysql is loaded.
         * Referencing it directly in an environment without pdo_mysql can cause
         * "Undefined constant PDO::MYSQL_ATTR_INIT_COMMAND".
         */
        $mysqlInitCommand = self::mysqlInitCommandConstant();

        if ($mysqlInitCommand !== null && self::hasMysqlDriver()) {
            $options[$mysqlInitCommand] = 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        return $options;
    }

    /**
     * Whether the current PHP runtime has the MySQL PDO driver loaded.
     */
    public static function hasMysqlDriver(): bool
    {
        return in_array('mysql', PDO::getAvailableDrivers(), true);
    }

    /**
     * Resolve PDO::MYSQL_ATTR_INIT_COMMAND safely.
     *
     * @return int|null
     */
    private static function mysqlInitCommandConstant(): ?int
    {
        if (!defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            return null;
        }

        $value = constant('PDO::MYSQL_ATTR_INIT_COMMAND');

        return is_int($value) ? $value : null;
    }
}