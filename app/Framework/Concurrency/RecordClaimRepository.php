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

namespace Catalyst\Framework\Concurrency;

use Catalyst\Entities\RecordClaim;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use DateTimeImmutable;

/**
 * Repository for tenant-scoped record claim rows.
 *
 * @package Catalyst\Framework\Concurrency
 * Responsibility: Reads, locks, searches, and decorates record claims for concurrency workflows.
 */
final class RecordClaimRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes database and logging collaborators for claim persistence.
     *
     * Responsibility: Initializes database and logging collaborators for claim persistence.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Finds the claim for a tenant resource record.
     *
     * Responsibility: Finds the claim for a tenant resource record.
     */
    public function findByResource(string $resourceKey, int $recordId): ?RecordClaim
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT * FROM record_claims
                 WHERE resource_key = ?
                   AND record_id = ?
                   AND tenant_id = ?
                 LIMIT 1',
                [$resourceKey, $recordId, $this->currentTenantId()]
            );
        } catch (\Throwable $e) {
            $this->logger->warning('RecordClaimRepository::findByResource failed', ['error' => $e->getMessage()]);

            return null;
        }

        return is_array($row) ? RecordClaim::fromRow($row) : null;
    }

    /**
     * Locks and returns the claim row for a resource inside an active transaction.
     *
     * Responsibility: Locks and returns the claim row for a resource inside an active transaction.
     */
    public function lockByResource(string $resourceKey, int $recordId): ?RecordClaim
    {
        $row = $this->db->connection()->selectOne(
            'SELECT * FROM record_claims
             WHERE resource_key = ?
               AND record_id = ?
               AND tenant_id = ?
             LIMIT 1 FOR UPDATE',
            [$resourceKey, $recordId, $this->currentTenantId()]
        );

        return is_array($row) ? RecordClaim::fromRow($row) : null;
    }

    /**
     * Searches claim rows using tenant, resource, record, actor, and active filters.
     *
     * Responsibility: Searches claim rows using tenant, resource, record, actor, and active filters.
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function search(array $filters = []): array
    {
        $conditions = [];
        $bindings = [];
        $activeOnly = !empty($filters['active']);
        $now = $this->now()->format('Y-m-d H:i:s');

        $conditions[] = 'tenant_id = ?';
        $bindings[] = $this->currentTenantId();

        $resourceKey = trim((string) ($filters['resource_key'] ?? ''));
        if ($resourceKey !== '') {
            $conditions[] = 'resource_key = ?';
            $bindings[] = $resourceKey;
        }

        $recordId = (int) ($filters['record_id'] ?? 0);
        if ($recordId > 0) {
            $conditions[] = 'record_id = ?';
            $bindings[] = $recordId;
        }

        $actorId = (int) ($filters['actor_id'] ?? 0);
        if ($actorId > 0) {
            $conditions[] = 'claimed_by = ?';
            $bindings[] = $actorId;
        }

        if ($activeOnly) {
            $conditions[] = 'released_at IS NULL AND (expires_at IS NULL OR expires_at > ?)';
            $bindings[] = $now;
        }

        $sql = 'SELECT * FROM record_claims';
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY updated_at DESC, id DESC';

        try {
            $rows = $this->db->connection()->select($sql, $bindings) ?: [];
        } catch (\Throwable $e) {
            $this->logger->warning('RecordClaimRepository::search failed', ['error' => $e->getMessage()]);

            return [];
        }

        return array_map(fn (array $row): array => $this->decorateRow($row), $rows);
    }

    /**
     * Adds status and expiry metadata to a raw claim row.
     *
     * Responsibility: Adds status and expiry metadata to a raw claim row.
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function decorateRow(array $row): array
    {
        $now = $this->now();
        $releasedAt = $this->parseDateTime($row['released_at'] ?? null);
        $expiresAt = $this->parseDateTime($row['expires_at'] ?? null);
        $status = 'active';

        if ($releasedAt instanceof DateTimeImmutable) {
            $status = 'released';
        } elseif ($expiresAt instanceof DateTimeImmutable && $expiresAt <= $now) {
            $status = 'expired';
        }

        $row['status'] = $status;
        $row['active'] = $status === 'active';
        $row['seconds_to_expiry'] = $expiresAt instanceof DateTimeImmutable
            ? max(0, $expiresAt->getTimestamp() - $now->getTimestamp())
            : null;

        return $row;
    }

    /**
     * Parses a database datetime string into an immutable timestamp.
     *
     * Responsibility: Parses a database datetime string into an immutable timestamp.
     */
    private function parseDateTime(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value) ?: null;
    }

    /**
     * Returns the current timestamp for claim status decoration.
     *
     * Responsibility: Returns the current timestamp for claim status decoration.
     */
    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /**
     * Resolves the active tenant id for all claim queries.
     *
     * Responsibility: Resolves the active tenant id for all claim queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
