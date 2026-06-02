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

use Catalyst\Helpers\Path\ProjectPath;
use RuntimeException;

/**
 * Runner for applying and rolling back database migrations.
 *
 * @package Catalyst\Framework\Database
 * Responsibility: Discovers migration files, tracks applied versions, executes batches, and maintains migration history.
 */
class MigrationRunner
{
    private const MIGRATIONS_TABLE = 'migrations';

    private string $migrationPath;

    /**
     * Initializes the runner with a database connection and migration directory.
     *
     * Responsibility: Initializes the runner with a database connection and migration directory.
     */
    public function __construct(
        private readonly Connection $connection,
        ?string $migrationPath = null
    ) {
        $this->migrationPath = $migrationPath ?? ProjectPath::migrations();
    }

    /**
     * Coordinates the run pending method responsibility within its owning class.
     *
     * Responsibility: Coordinates the run pending method responsibility within its owning class.
     * @return array<int, array{version:string,name:string,batch:int}>
     */
    public function runPending(): array
    {
        $this->ensureMigrationsTable();

        $migrations = $this->discoverMigrations();
        $applied    = $this->getAppliedVersions();
        $pending    = array_diff_key($migrations, $applied);

        if ($pending === []) {
            return [];
        }

        $batch = $this->nextBatchNumber();
        $ran   = [];

        foreach ($pending as $version => $entry) {
            $version = (string) $version;

            $entry['migration']->setConnection($this->connection)->up();
            $this->recordAppliedMigration($version, $entry['name'], $batch);

            $ran[] = [
                'version' => $version,
                'name'    => $entry['name'],
                'batch'   => $batch,
            ];
        }

        return $ran;
    }

    /**
     * Coordinates the rollback last batch method responsibility within its owning class.
     *
     * Responsibility: Coordinates the rollback last batch method responsibility within its owning class.
     * @return array<int, array{version:string,name:string,batch:int}>
     */
    public function rollbackLastBatch(): array
    {
        if (!$this->migrationsTableExists()) {
            return [];
        }

        $batch = $this->lastBatchNumber();
        if ($batch === null) {
            return [];
        }

        $migrations = $this->discoverMigrations();
        $applied    = $this->connection->select(
            'SELECT version, name, batch
             FROM migrations
             WHERE batch = :batch
             ORDER BY version DESC',
            [':batch' => $batch]
        );

        $rolledBack = [];

        foreach ($applied as $row) {
            $version = (string) $row['version'];

            if (!isset($migrations[$version])) {
                throw new RuntimeException(
                    "Cannot rollback migration '{$version}': file is missing from {$this->migrationPath}"
                );
            }

            $migrations[$version]['migration']->setConnection($this->connection)->down();
            $this->removeAppliedMigration($version);

            $rolledBack[] = [
                'version' => $version,
                'name'    => (string) $row['name'],
                'batch'   => (int) $row['batch'],
            ];
        }

        return $rolledBack;
    }

    /**
     * Coordinates the status method responsibility within its owning class.
     *
     * Responsibility: Coordinates the status method responsibility within its owning class.
     * @return array{repository_exists:bool,migrations:array<int, array{version:string,name:string,status:string,batch:?int,ran_at:?string}>}
     */
    public function status(): array
    {
        $migrations       = $this->discoverMigrations();
        $repositoryExists = $this->migrationsTableExists();
        $applied          = $repositoryExists ? $this->getAppliedVersions() : [];
        $status           = [];

        foreach ($migrations as $version => $entry) {
            $version    = (string) $version;
            $appliedRow = $applied[$version] ?? null;

            $status[] = [
                'version' => $version,
                'name'    => $entry['name'],
                'status'  => $appliedRow === null ? 'pending' : 'ran',
                'batch'   => $appliedRow['batch'] ?? null,
                'ran_at'  => $appliedRow['ran_at'] ?? null,
            ];
        }

        return [
            'repository_exists' => $repositoryExists,
            'migrations'        => $status,
        ];
    }

    /**
     * Returns the filesystem path where migration files are discovered.
     *
     * Responsibility: Returns the filesystem path where migration files are discovered.
     */
    public function getMigrationPath(): string
    {
        return $this->migrationPath;
    }

    /**
     * Creates the migrations tracking table when it is missing.
     *
     * Responsibility: Creates the migrations tracking table when it is missing.
     */
    private function ensureMigrationsTable(): void
    {
        $this->connection->getPdo()->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(14) NOT NULL,
                name VARCHAR(191) NOT NULL,
                batch INT UNSIGNED NOT NULL,
                ran_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_migrations_version (version),
                INDEX idx_migrations_batch (batch)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Determines whether the migrations tracking table exists.
     *
     * Responsibility: Determines whether the migrations tracking table exists.
     */
    private function migrationsTableExists(): bool
    {
        $row = $this->connection->selectOne(
            'SELECT 1
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table
             LIMIT 1',
            [':table' => self::MIGRATIONS_TABLE]
        );

        return $row !== null;
    }

    /**
     * Coordinates the discover migrations method responsibility within its owning class.
     *
     * Responsibility: Coordinates the discover migrations method responsibility within its owning class.
     * @return array<string, array{migration:Migration,name:string,file:string}>
     */
    private function discoverMigrations(): array
    {
        if (!is_dir($this->migrationPath)) {
            return [];
        }

        $migrations = [];
        $files      = glob($this->migrationPath . DS . '*.php') ?: [];
        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $migration = require $file;

            if (!$migration instanceof Migration) {
                throw new RuntimeException(
                    "Migration file must return an instance of " . Migration::class . ": {$file}"
                );
            }

            $version = trim($migration->getVersion());
            if (!preg_match('/^\d{14}$/', $version)) {
                throw new RuntimeException(
                    "Migration version must be a 14-digit timestamp in {$file}"
                );
            }

            if (isset($migrations[$version])) {
                throw new RuntimeException("Duplicate migration version detected: {$version}");
            }

            $migrations[$version] = [
                'migration' => $migration,
                'name'      => pathinfo($file, PATHINFO_FILENAME),
                'file'      => $file,
            ];
        }

        ksort($migrations, SORT_STRING);

        return $migrations;
    }

    /**
     * Coordinates the get applied versions method responsibility within its owning class.
     *
     * Responsibility: Coordinates the get applied versions method responsibility within its owning class.
     * @return array<string, array{batch:int,ran_at:?string,name:string}>
     */
    private function getAppliedVersions(): array
    {
        if (!$this->migrationsTableExists()) {
            return [];
        }

        $rows    = $this->connection->select(
            'SELECT version, name, batch, ran_at
             FROM migrations
             ORDER BY version ASC'
        );
        $applied = [];

        foreach ($rows as $row) {
            $applied[(string) $row['version']] = [
                'batch'  => (int) $row['batch'],
                'ran_at' => isset($row['ran_at']) ? (string) $row['ran_at'] : null,
                'name'   => (string) $row['name'],
            ];
        }

        return $applied;
    }

    /**
     * Calculates the next migration batch number.
     *
     * Responsibility: Calculates the next migration batch number.
     */
    private function nextBatchNumber(): int
    {
        $row = $this->connection->selectOne('SELECT MAX(batch) AS max_batch FROM migrations');
        $max = isset($row['max_batch']) ? (int) $row['max_batch'] : 0;

        return $max + 1;
    }

    /**
     * Resolves the latest applied migration batch number.
     *
     * Responsibility: Resolves the latest applied migration batch number.
     */
    private function lastBatchNumber(): ?int
    {
        $row = $this->connection->selectOne('SELECT MAX(batch) AS max_batch FROM migrations');

        if ($row === null || $row['max_batch'] === null) {
            return null;
        }

        return (int) $row['max_batch'];
    }

    /**
     * Records a successfully applied migration version and batch.
     *
     * Responsibility: Records a successfully applied migration version and batch.
     */
    private function recordAppliedMigration(string $version, string $name, int $batch): void
    {
        $this->connection->execute(
            'INSERT INTO migrations (version, name, batch) VALUES (:version, :name, :batch)',
            [
                ':version' => $version,
                ':name'    => $name,
                ':batch'   => $batch,
            ]
        );
    }

    /**
     * Removes a migration version from the applied migration history.
     *
     * Responsibility: Removes a migration version from the applied migration history.
     */
    private function removeAppliedMigration(string $version): void
    {
        $this->connection->execute(
            'DELETE FROM migrations WHERE version = :version',
            [':version' => $version]
        );
    }
}
