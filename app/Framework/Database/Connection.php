<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database;

use Catalyst\Helpers\Exceptions\ConnectionException;
use Catalyst\Helpers\Exceptions\QueryException;
use Catalyst\Helpers\Log\Logger;
use Closure;
use Exception;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Database connection wrapper
 *
 * Encapsulates PDO connection and provides methods for executing
 * queries and managing transactions.
 *
 * @package Catalyst\Framework\Database
 */
class Connection
{
    protected ?PDO $pdo = null;

    protected Logger $logger;

    protected string $name;

    protected array $params;

    public function __construct(
        string $host,
        int    $port,
        string $database,
        string $username,
        string $password,
        string $name
    ) {
        $this->logger = Logger::getInstance();
        $this->name   = $name;
        $this->params = [
            'host'     => $host,
            'port'     => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
        ];
    }

    /**
     * Get the PDO instance, connecting lazily if necessary.
     *
     * @throws ConnectionException
     */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    /**
     * Establish PDO connection.
     *
     * @throws ConnectionException
     */
    protected function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $this->params['host'],
                $this->params['port'],
                $this->params['database']
            );

            $options = PdoOptionsFactory::mysql();

            $this->pdo = new PDO(
                $dsn,
                $this->params['username'],
                $this->params['password'],
                $options
            );

            $this->logger->debug("Database connection '{$this->name}' established");
        } catch (PDOException $e) {
            $this->logger->error("Database connection '{$this->name}' failed", [
                'error' => $e->getMessage(),
            ]);

            throw new ConnectionException(
                "Failed to connect to database '{$this->name}': " . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * Create a new QueryBuilder for the given table.
     */
    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    /**
     * Execute a prepared SQL query and return the statement.
     *
     * @throws QueryException
     */
    public function query(string $query, array $params = []): PDOStatement
    {
        try {
            $statement = $this->getPdo()->prepare($query);
            $statement->execute($params);
            return $statement;
        } catch (PDOException $e) {
            $this->logger->error('Query execution failed', [
                'connection' => $this->name,
                'query'      => $query,
                'params'     => $params,
                'error'      => $e->getMessage(),
            ]);

            throw new QueryException(
                'Query execution failed: ' . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * Execute a SELECT query and return all rows.
     *
     * @throws QueryException
     */
    public function select(string $query, array $params = [], int $fetchMode = PDO::FETCH_ASSOC): array
    {
        return $this->query($query, $params)->fetchAll($fetchMode);
    }

    /**
     * Execute a SELECT query and return a single row, or null if not found.
     *
     * @throws QueryException
     */
    public function selectOne(string $query, array $params = [], int $fetchMode = PDO::FETCH_ASSOC): ?array
    {
        $result = $this->query($query, $params)->fetch($fetchMode);
        return $result !== false ? $result : null;
    }

    /**
     * Execute a non-SELECT statement and return the number of affected rows.
     *
     * @throws QueryException
     */
    public function execute(string $query, array $params = []): int
    {
        return $this->query($query, $params)->rowCount();
    }

    /**
     * Insert a row into a table and return the last insert ID.
     *
     * @throws QueryException
     */
    public function insert(string $table, array $data): int
    {
        $table = SqlReference::assertTable($table);
        $columns      = array_keys($data);
        $columns      = array_map(
            static fn (string $column): string => SqlReference::assertColumn($column, 'insert column'),
            $columns
        );
        $placeholders = array_map(fn ($col) => ":$col", $columns);

        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $params = [];
        foreach ($data as $column => $value) {
            $params[":$column"] = $value;
        }

        $this->query($query, $params);
        return (int)$this->getPdo()->lastInsertId();
    }

    /**
     * Execute a callback within a database transaction.
     * Automatically commits on success and rolls back on exception.
     *
     * @throws QueryException
     */
    public function transaction(Closure $callback): mixed
    {
        try {
            $this->getPdo()->beginTransaction();
            $this->logger->debug("Transaction started on '{$this->name}'");

            $result = $callback($this);

            $this->getPdo()->commit();
            $this->logger->debug("Transaction committed on '{$this->name}'");

            return $result;
        } catch (QueryException $e) {
            $this->rollback();
            throw $e;
        } catch (Exception $e) {
            $this->rollback();
            throw new QueryException(
                'Transaction failed: ' . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * Test the connection by executing a trivial query.
     */
    public function test(): bool
    {
        try {
            $this->getPdo()->query('SELECT 1');
            return true;
        } catch (ConnectionException|PDOException) {
            return false;
        }
    }

    /**
     * Return connection parameters with the password masked.
     */
    public function getConnectionInfo(): array
    {
        $info             = $this->params;
        $info['password'] = '********';
        return $info;
    }

    /**
     * Return the connection name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    protected function rollback(): void
    {
        if ($this->pdo !== null && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
            $this->logger->debug("Transaction rolled back on '{$this->name}'");
        }
    }
}
