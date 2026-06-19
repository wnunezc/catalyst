<?php

declare(strict_types=1);

namespace CatalystTest\Integration\Support;

use Catalyst\Framework\Database\Connection;
use CatalystTest\TestCase;
use PDO;
use PDOException;
use RuntimeException;

abstract class MySqlIntegrationTestCase extends TestCase
{
    private ?PDO $adminPdo = null;
    private ?PDO $pdo = null;
    private ?string $databaseName = null;

    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/requirement-loader/error-catcher.php';

        $settings = $this->databaseSettings();
        $this->databaseName = 'catalyst_test_' . getmypid() . '_' . bin2hex(random_bytes(4));
        $this->adminPdo = $this->connect(null, $settings);
        $this->adminPdo->exec(
            'CREATE DATABASE `' . str_replace('`', '``', $this->databaseName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
        $this->pdo = $this->connect($this->databaseName, $settings);
    }

    public function tearDown(): void
    {
        if ($this->adminPdo !== null && $this->databaseName !== null) {
            $this->adminPdo->exec('DROP DATABASE IF EXISTS `' . str_replace('`', '``', $this->databaseName) . '`');
        }

        $this->pdo = null;
        $this->adminPdo = null;
        $this->databaseName = null;
    }

    protected function pdo(): PDO
    {
        if ($this->pdo === null) {
            throw new RuntimeException('Integration database has not been initialized.');
        }

        return $this->pdo;
    }

    protected function connection(): Connection
    {
        $pdo = $this->pdo();

        return new class ($pdo) extends Connection {
            public function __construct(PDO $pdo)
            {
                parent::__construct('', 0, '', '', '', 'mysql-integration-test');
                $this->pdo = $pdo;
            }
        };
    }

    /**
     * @return array{host: string, port: int, username: string, password: string}
     */
    private function databaseSettings(): array
    {
        $root = dirname(__DIR__, 4);
        $db = $this->jsonFile($root . '/boot-core/config/development/db.json');
        $secrets = $this->jsonFile($root . '/boot-core/config/development/secrets.json');
        $db1 = is_array($db['db1'] ?? null) ? $db['db1'] : [];
        $secretDb1 = is_array($secrets['db']['db1'] ?? null) ? $secrets['db']['db1'] : [];

        return [
            'host' => (string) (getenv('CATALYST_TEST_DB_HOST') ?: '127.0.0.1'),
            'port' => (int) (getenv('CATALYST_TEST_DB_PORT') ?: ($db1['db_port'] ?? 3306)),
            'username' => (string) (getenv('CATALYST_TEST_DB_USERNAME') ?: ($db1['db_username'] ?? 'root')),
            'password' => (string) (getenv('CATALYST_TEST_DB_PASSWORD') ?: ($secretDb1['db_password'] ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonFile(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array{host: string, port: int, username: string, password: string} $settings
     */
    private function connect(?string $database, array $settings): PDO
    {
        $dsn = 'mysql:host=' . $settings['host'] . ';port=' . $settings['port']
            . ($database !== null ? ';dbname=' . $database : '')
            . ';charset=utf8mb4';

        try {
            return new PDO($dsn, $settings['username'], $settings['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('MySQL/MariaDB integration database is unavailable: ' . $exception->getMessage(), 0, $exception);
        }
    }
}
