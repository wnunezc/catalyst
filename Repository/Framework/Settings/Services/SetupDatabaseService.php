<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Services;

use Catalyst\Framework\Database\PdoOptionsFactory;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Path\ProjectPath;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/**
 * Defines the Setup Database Service class contract.
 *
 * @package Catalyst\Repository\Settings\Services
 * Responsibility: Coordinates the setup database service behavior within its module boundary.
 */
final class SetupDatabaseService
{
    /**
 * Initializes the Setup Database Service instance.
 */
public function __construct(
        private readonly ConfigManager $config
    ) {
    }

    /**
 * Creates the requested object.
 */
public static function make(): self
    {
        return new self(ConfigManager::getInstance());
    }

    /**
     * Opens the setup database, creates it if it is missing and ensures the
     * minimal auth schema required by the setup wizard exists.
     *
     * @throws SetupDatabaseException
     */
    public function open(): PDO
    {
        $this->assertConfigFilesExist();

        $dbCfg = $this->config->section('db')['db1'] ?? null;
        if (!is_array($dbCfg) || ($dbCfg['db_database'] ?? '') === '') {
            throw new SetupDatabaseException('settings.completion.errors.db_incomplete', 422);
        }

        try {
            $pdo = $this->connectOrCreateDatabase($dbCfg);
        } catch (Throwable $e) {
            throw new SetupDatabaseException(
                'settings.completion.errors.db_unreachable',
                422,
                $e->getMessage(),
                $e
            );
        }

        try {
            $this->ensureSetupSchema($pdo);
        } catch (Throwable $e) {
            throw new SetupDatabaseException(
                'settings.completion.errors.auth_tables_missing',
                500,
                $e->getMessage(),
                $e
            );
        }

        return $pdo;
    }

    /**
     * @throws SetupDatabaseException
     */
    private function assertConfigFilesExist(): void
    {
        $configDir = implode(DS, [PD, 'boot-core', 'config', $this->resolveEnv()]);
        $appJson = $configDir . DS . 'app.json';
        $dbJson = $configDir . DS . 'db.json';

        if (!is_file($appJson)) {
            throw new SetupDatabaseException('settings.completion.errors.app_json_missing', 422);
        }

        if (!is_file($dbJson)) {
            throw new SetupDatabaseException('settings.completion.errors.db_json_missing', 422);
        }
    }

    /**
 * Handles the resolve env workflow.
 */
private function resolveEnv(): string
    {
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            return 'development';
        }

        if (defined('IS_STAGING') && IS_STAGING) {
            return 'staging';
        }

        if (defined('IS_TESTING') && IS_TESTING) {
            return 'testing';
        }

        return 'production';
    }

    /**
     * @param array<string, mixed> $dbCfg
     *
     * @throws PDOException|RuntimeException
     */
    private function connectOrCreateDatabase(array $dbCfg): PDO
    {
        $host = (string) ($dbCfg['db_host'] ?? 'localhost');
        $port = (int) ($dbCfg['db_port'] ?? 3306);
        $db = (string) ($dbCfg['db_database'] ?? '');
        $user = (string) ($dbCfg['db_username'] ?? '');
        $pass = (string) ($dbCfg['db_password'] ?? '');

        $options = PdoOptionsFactory::mysql();

        try {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $db);
            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            if ((int) $e->getCode() !== 1049 && !str_contains($e->getMessage(), 'Unknown database')) {
                throw $e;
            }
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $db)) {
            throw new RuntimeException('Invalid database name (allowed: A-Z, a-z, 0-9, _): ' . $db);
        }

        $dsnHost = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
        $bootstrap = new PDO($dsnHost, $user, $pass, $options);
        $bootstrap->exec(
            "CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        unset($bootstrap);

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $db);

        return new PDO($dsn, $user, $pass, $options);
    }

    /**
     * @throws RuntimeException
     */
    private function ensureSetupSchema(PDO $pdo): void
    {
        $required = ['users', 'roles', 'user_roles'];
        $missing = array_filter(
            $required,
            fn (string $table): bool => !$this->tableExists($pdo, $table)
        );

        if ($missing === []) {
            return;
        }

        $this->runMigrationSql($pdo);

        $stillMissing = array_filter(
            $required,
            fn (string $table): bool => !$this->tableExists($pdo, $table)
        );

        if ($stillMissing !== []) {
            throw new RuntimeException(
                'Missing required setup tables: ' . implode(', ', $stillMissing)
            );
        }
    }

    /**
 * Determines whether the table exists.
 */
private function tableExists(PDO $pdo, string $table): bool
    {
        try {
            $stmt = $pdo->prepare(
                "SELECT 1 FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = :t LIMIT 1"
            );
            $stmt->execute([':t' => $table]);

            return $stmt->fetchColumn() !== false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @throws RuntimeException
     */
    private function runMigrationSql(PDO $pdo): void
    {
        $sqlFile = ProjectPath::database('create-catalyst-db.sql');
        if (!is_file($sqlFile)) {
            throw new RuntimeException('Migration file not found: ' . $sqlFile);
        }

        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new RuntimeException('Unable to read migration file: ' . $sqlFile);
        }

        $sql = (string) preg_replace('/--[^\n]*/', '', $sql);
        $rawStatements = explode(';', $sql);

        foreach ($rawStatements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') {
                continue;
            }

            if ($this->shouldSkipStatement($stmt)) {
                continue;
            }

            try {
                $pdo->exec($stmt);
            } catch (Throwable $e) {
                $preview = substr($stmt, 0, 200);

                throw new RuntimeException(
                    'Migration statement failed: ' . $e->getMessage()
                    . ' | SQL: ' . $preview,
                    0,
                    $e
                );
            }
        }
    }

    /**
 * Determines whether should skip statement.
 */
private function shouldSkipStatement(string $stmt): bool
    {
        $head = ltrim($stmt);

        if (preg_match('/^CREATE\s+DATABASE\b/i', $head)) {
            return true;
        }

        if (preg_match('/^USE\s+/i', $head)) {
            return true;
        }

        if (preg_match('/^INSERT\s+(?:IGNORE\s+)?INTO\s+users\b/i', $head)) {
            return true;
        }

        if (preg_match('/^INSERT\s+(?:IGNORE\s+)?INTO\s+user_roles\b/i', $head)) {
            return true;
        }

        return false;
    }
}