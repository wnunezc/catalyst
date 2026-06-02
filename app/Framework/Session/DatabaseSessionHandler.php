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

namespace Catalyst\Framework\Session;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Helpers\Log\Logger;
use PDO;
use SessionHandlerInterface;
use Throwable;

/**
 * Defines the Database Session Handler class contract.
 *
 * @package Catalyst\Framework\Session
 * Responsibility: Coordinates the database session handler behavior within its module boundary.
 */
final class DatabaseSessionHandler implements SessionHandlerInterface
{
    private bool $tableReady = false;

    /**
     * Initializes the Database Session Handler instance.
     */
    public function __construct(
        private readonly string $connectionName,
        private readonly string $tableName
    ) {
    }

    /**
     * Handles the open workflow.
     */
    public function open(string $savePath, string $sessionName): bool
    {
        return $this->ensureTable();
    }

    /**
     * Handles the close workflow.
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Reads the requested value.
     */
    public function read(string $id): string|false
    {
        if (!$this->ensureTable()) {
            return '';
        }

        try {
            $row = $this->pdo()->prepare(
                sprintf('SELECT payload FROM %s WHERE id = :id LIMIT 1', $this->tableName)
            );
            $row->execute([':id' => $id]);
            $payload = $row->fetchColumn();

            return is_string($payload) ? $payload : '';
        } catch (Throwable $e) {
            Logger::getInstance()->error('Database session read failed', [
                'table' => $this->tableName,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * Writes the requested value.
     */
    public function write(string $id, string $data): bool
    {
        if (!$this->ensureTable()) {
            return false;
        }

        try {
            $statement = $this->pdo()->prepare(
                sprintf(
                    'INSERT INTO %s (id, payload, last_activity, ip_address, user_agent)
                     VALUES (:id, :payload, :last_activity, :ip_address, :user_agent)
                     ON DUPLICATE KEY UPDATE
                        payload = VALUES(payload),
                        last_activity = VALUES(last_activity),
                        ip_address = VALUES(ip_address),
                        user_agent = VALUES(user_agent)',
                    $this->tableName
                )
            );

            return $statement->execute([
                ':id' => $id,
                ':payload' => $data,
                ':last_activity' => time(),
                ':ip_address' => $this->clientIp(),
                ':user_agent' => $this->userAgent(),
            ]);
        } catch (Throwable $e) {
            Logger::getInstance()->error('Database session write failed', [
                'table' => $this->tableName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handles the destroy workflow.
     */
    public function destroy(string $id): bool
    {
        if (!$this->ensureTable()) {
            return false;
        }

        try {
            $statement = $this->pdo()->prepare(
                sprintf('DELETE FROM %s WHERE id = :id', $this->tableName)
            );

            return $statement->execute([':id' => $id]);
        } catch (Throwable $e) {
            Logger::getInstance()->error('Database session destroy failed', [
                'table' => $this->tableName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handles the gc workflow.
     */
    public function gc(int $max_lifetime): int|false
    {
        if (!$this->ensureTable()) {
            return false;
        }

        try {
            $statement = $this->pdo()->prepare(
                sprintf('DELETE FROM %s WHERE last_activity < :expires_before', $this->tableName)
            );
            $statement->execute([
                ':expires_before' => time() - $max_lifetime,
            ]);

            return $statement->rowCount();
        } catch (Throwable $e) {
            Logger::getInstance()->error('Database session GC failed', [
                'table' => $this->tableName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handles the ensure table workflow.
     */
    private function ensureTable(): bool
    {
        if ($this->tableReady) {
            return true;
        }

        try {
            $this->pdo()->exec(sprintf(
                'CREATE TABLE IF NOT EXISTS %s (
                    id VARCHAR(128) NOT NULL PRIMARY KEY,
                    payload LONGTEXT NOT NULL,
                    last_activity INT UNSIGNED NOT NULL,
                    ip_address VARCHAR(64) DEFAULT NULL,
                    user_agent VARCHAR(255) DEFAULT NULL,
                    INDEX idx_sessions_last_activity (last_activity)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
                $this->tableName
            ));

            $this->tableReady = true;

            return true;
        } catch (Throwable $e) {
            Logger::getInstance()->error('Database session table bootstrap failed', [
                'table' => $this->tableName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handles the pdo workflow.
     */
    private function pdo(): PDO
    {
        return DatabaseManager::getInstance()->connection($this->connectionName)->getPdo();
    }

    /**
     * Handles the client ip workflow.
     */
    private function clientIp(): ?string
    {
        $value = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? null;

        if (!is_string($value) || $value === '') {
            return null;
        }

        if (str_contains($value, ',')) {
            $segments = explode(',', $value);
            return trim((string) $segments[0]);
        }

        return $value;
    }

    /**
     * Handles the user agent workflow.
     */
    private function userAgent(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        return is_string($userAgent) && $userAgent !== '' ? substr($userAgent, 0, 255) : null;
    }
}
