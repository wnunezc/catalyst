<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * @package Catalyst\Repository\DevTools\Services
 */

namespace Catalyst\Repository\DevTools\Services;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Database\MigrationRunner;
use Catalyst\Helpers\Path\ProjectPath;
use PDO;
use RuntimeException;

/**
 * Development-only database reset workflow.
 *
 * Keeps destructive SQL orchestration out of the HTTP controller while preserving
 * the existing dev-only reset behavior and schema replay sequence.
 */
final class DatabaseResetService
{
    /**
     * Optional development-only SQL overlay replayed after the canonical schema.
     */
    private const string DEVELOPMENT_OVERLAY_FILE = 'create-catalyst-db.development.sql';

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
     * Execute a .sql file against the active PDO connection.
     *
     * The SQL file comes from Catalyst-controlled database assets. Statements
     * that switch/create databases are skipped because the connection already
     * targets the configured schema.
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

    private function executeDevelopmentOverlay(PDO $pdo): void
    {
        $path = ProjectPath::database(self::DEVELOPMENT_OVERLAY_FILE);

        if (!is_file($path)) {
            return;
        }

        $this->executeSqlFile($pdo, $path);
    }

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
