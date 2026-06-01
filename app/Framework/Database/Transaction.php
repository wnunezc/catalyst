<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database;

use Catalyst\Helpers\Exceptions\QueryException;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Database transaction handler
 *
 * Provides explicit begin / commit / rollback control over a Connection,
 * complementing the closure-based Connection::transaction() for cases
 * where manual transaction control is needed.
 *
 * @package Catalyst\Framework\Database
 */
class Transaction
{
    protected Logger $logger;

    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->logger     = Logger::getInstance();
        $this->connection = $connection;
    }

    /**
     * Begin a new transaction.
     *
     * @throws QueryException
     */
    public function begin(): self
    {
        try {
            $this->connection->getPdo()->beginTransaction();
            $this->logger->debug("Transaction started on '{$this->connection->getName()}'");
        } catch (Exception $e) {
            throw new QueryException('Failed to begin transaction: ' . $e->getMessage(), 0, $e);
        }
        return $this;
    }

    /**
     * Commit the active transaction.
     *
     * @throws QueryException
     */
    public function commit(): self
    {
        try {
            $this->connection->getPdo()->commit();
            $this->logger->debug("Transaction committed on '{$this->connection->getName()}'");
        } catch (Exception $e) {
            throw new QueryException('Failed to commit transaction: ' . $e->getMessage(), 0, $e);
        }
        return $this;
    }

    /**
     * Roll back the active transaction (no-op if none is active).
     *
     * @throws QueryException
     */
    public function rollback(): self
    {
        try {
            $pdo = $this->connection->getPdo();
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
                $this->logger->debug("Transaction rolled back on '{$this->connection->getName()}'");
            }
        } catch (Exception $e) {
            throw new QueryException('Failed to roll back transaction: ' . $e->getMessage(), 0, $e);
        }
        return $this;
    }
}
