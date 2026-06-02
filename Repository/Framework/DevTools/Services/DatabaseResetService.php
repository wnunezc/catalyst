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

namespace Catalyst\Repository\DevTools\Services;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Database\MigrationRunner;
use Catalyst\Helpers\Path\ProjectPath;
use PDO;
use RuntimeException;

/**
 * Rebuilds the development database from canonical SQL and migrations.
 *
 * @package Catalyst\Repository\DevTools\Services
 * Responsibility: Orchestrates destructive DevTools database reset operations.
 */
final class DatabaseResetService
{
    /**
     * Optional development-only SQL overlay replayed after the canonical schema.
     */
    private const string DEVELOPMENT_OVERLAY_FILE = 'create-catalyst-db.development.sql';

    /**
     * Drops current tables and replays the canonical development schema.
     *
     * Responsibility: Drops current tables and replays the canonical development schema.
     */
    public function reset(): void
    {
        $db = DatabaseManager::getInstance()->connection();
        $pdo = $db->getPdo();

        $this->dropAllTables($pdo);
        $this->executeSqlFile($pdo, ProjectPath::database('create-catalyst-db.sql'));

        $runner = new MigrationRunner($db);
        $runner->runPending();

        $this->executeDevelopmentOverlay($pdo);
    }

    /**
     * Executes a Catalyst-controlled SQL file against the active connection.
     *
     * Responsibility: Executes a Catalyst-controlled SQL file against the active connection.
     */
    private function executeSqlFile(PDO $pdo, string $path): void
    {
        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new RuntimeException("Cannot read SQL file: {$path}");
        }

        $sql = preg_replace('/--[^\n]*\n/', "\n", $sql) ?? $sql;
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql) ?? $sql;

        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            static fn(string $statement): bool => $statement !== ''
        );

        foreach ($statements as $statement) {
            if (preg_match('/^\s*(CREATE\s+DATABASE|USE\s+)/i', $statement) === 1) {
                continue;
            }

            $pdo->exec($statement);
        }
    }

    /**
     * Replays the optional development SQL overlay when present.
     *
     * Responsibility: Replays the optional development SQL overlay when present.
     */
    private function executeDevelopmentOverlay(PDO $pdo): void
    {
        $path = ProjectPath::database(self::DEVELOPMENT_OVERLAY_FILE);

        if (!is_file($path)) {
            return;
        }

        $this->executeSqlFile($pdo, $path);
    }

    /**
     * Drops every table in the active schema with foreign-key checks disabled.
     *
     * Responsibility: Drops every table in the active schema with foreign-key checks disabled.
     */
    private function dropAllTables(PDO $pdo): void
    {
        $tables = $pdo->query('SHOW TABLES');

        if ($tables === false) {
            return;
        }

        $tableNames = $tables->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tableNames as $tableName) {
                $table = trim((string) $tableName);

                if ($table === '') {
                    continue;
                }

                $pdo->exec(sprintf('DROP TABLE IF EXISTS `%s`', str_replace('`', '``', $table)));
            }
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
