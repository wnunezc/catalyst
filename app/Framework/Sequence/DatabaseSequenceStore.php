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

namespace Catalyst\Framework\Sequence;

use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\DatabaseManager;

/**
 * Database-backed scoped sequence store.
 *
 * @package Catalyst\Framework\Sequence
 * Responsibility: Advances scoped sequence counters inside a database transaction with row locking.
 */
final class DatabaseSequenceStore implements SequenceStoreInterface
{
    /**
     * Initializes the sequence store with a database connection.
     *
     * Responsibility: Binds sequence persistence to a PDO connection while keeping manager logic storage-neutral.
     */
    public function __construct(private ?Connection $connection = null)
    {
    }

    /**
     * Atomically advances and returns the next sequence number.
     *
     * Responsibility: Owns concurrency-safe increment semantics for scoped sequence counters.
     */
    public function next(int $tenantId, string $scopeKey, string $sequenceKey, int $startAt = 1, int $step = 1): int
    {
        $connection = $this->connection ?? DatabaseManager::getInstance()->connection();

        return (int)$connection->transaction(function (Connection $db) use ($tenantId, $scopeKey, $sequenceKey, $startAt, $step): int {
            $row = $db->selectOne(
                'SELECT id, current_value FROM framework_sequences WHERE tenant_id = ? AND scope_key = ? AND sequence_key = ? FOR UPDATE',
                [$tenantId, $scopeKey, $sequenceKey]
            );

            if ($row === null) {
                $next = $startAt;
                $db->execute(
                    'INSERT INTO framework_sequences (tenant_id, scope_key, sequence_key, current_value, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
                    [$tenantId, $scopeKey, $sequenceKey, $next, gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s')]
                );

                return $next;
            }

            $next = (int)$row['current_value'] + $step;
            $db->execute(
                'UPDATE framework_sequences SET current_value = ?, updated_at = ? WHERE id = ?',
                [$next, gmdate('Y-m-d H:i:s'), (int)$row['id']]
            );

            return $next;
        });
    }
}