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
 * Responsibility: Provides explicit begin, commit and rollback operations over one connection.
 */
class Transaction
{
    protected Logger $logger;

    protected Connection $connection;

    /**
     * Initializes the Transaction instance.
     *
     * Responsibility: Initializes the Transaction instance.
     */
    public function __construct(Connection $connection)
    {
        $this->logger     = Logger::getInstance();
        $this->connection = $connection;
    }

    /**
     * Begin a new transaction.
     *
     * Responsibility: Begin a new transaction.
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
     * Responsibility: Commit the active transaction.
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
     * Responsibility: Roll back the active transaction (no-op if none is active).
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
