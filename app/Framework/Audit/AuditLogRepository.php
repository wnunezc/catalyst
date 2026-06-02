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

namespace Catalyst\Framework\Audit;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Reads tenant-scoped audit log rows for admin inspection.
 *
 * @package Catalyst\Framework\Audit
 * Responsibility: Search, decode and filter audit records without exposing write-side audit logic.
 */
final class AuditLogRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes database and logging collaborators for audit reads.
     *
     * Responsibility: Initializes database and logging collaborators for audit reads.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Searches audit log summary rows with filters, sorting and pagination.
     *
     * Responsibility: Searches audit log summary rows with filters, sorting and pagination.
     * @param array<string, mixed> $criteria
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 20));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $channel = trim((string) ($criteria['channel'] ?? ''));
        $action = trim((string) ($criteria['action'] ?? ''));
        $resource = trim((string) ($criteria['resource'] ?? ''));
        $sort = $this->resolveSortColumn((string) ($criteria['sort'] ?? 'occurred_at'));
        $direction = $this->resolveSortDirection((string) ($criteria['direction'] ?? 'desc'));

        $where = [];
        $bindings = [];
        $where[] = 'tenant_id = ?';
        $bindings[] = $this->currentTenantId();

        if ($search !== '') {
            $where[] = '(resource LIKE ? OR COALESCE(resource_label, \'\') LIKE ? OR COALESCE(request_uri, \'\') LIKE ? OR COALESCE(event_name, \'\') LIKE ?)';
            $needle = '%' . $search . '%';
            array_push($bindings, $needle, $needle, $needle, $needle);
        }

        if ($channel !== '') {
            $where[] = 'channel = ?';
            $bindings[] = $channel;
        }

        if ($action !== '') {
            $where[] = 'action = ?';
            $bindings[] = $action;
        }

        if ($resource !== '') {
            $where[] = 'resource = ?';
            $bindings[] = $resource;
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM audit_logs' . $whereSql,
                $bindings
            );
            $rows = $this->db->connection()->select(
                'SELECT id, tenant_id, tenant_key, channel, event_name, action, resource, resource_id, resource_label, actor_id, actor_type, request_method, request_uri, ip_address, user_agent, occurred_at
                 FROM audit_logs'
                . $whereSql
                . ' ORDER BY ' . $sort . ' ' . $direction
                . ' LIMIT ? OFFSET ?',
                array_merge($bindings, [$perPage, $offset])
            ) ?: [];

            return [
                'rows' => $rows,
                'total' => (int) ($totalRow['aggregate'] ?? 0),
            ];
        } catch (Exception $e) {
            $this->logger->warning('AuditLogRepository::search failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }
    }

    /**
     * Loads a single audit row and decodes its JSON payload fields.
     *
     * Responsibility: Loads a single audit row and decodes its JSON payload fields.
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT * FROM audit_logs WHERE id = ? AND tenant_id = ?',
                [$id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('AuditLogRepository::find failed', ['error' => $e->getMessage()]);
            return null;
        }

        if ($row === null) {
            return null;
        }

        foreach (['before_payload', 'after_payload', 'metadata'] as $field) {
            if (isset($row[$field]) && is_string($row[$field]) && $row[$field] !== '') {
                $decoded = json_decode($row[$field], true);
                $row[$field] = is_array($decoded) ? $decoded : [];
                continue;
            }

            $row[$field] = [];
        }

        return $row;
    }

    /**
     * Lists distinct audit channels available for the current tenant.
     *
     * Responsibility: Lists distinct audit channels available for the current tenant.
     * @return string[]
     */
    public function distinctChannels(): array
    {
        return $this->distinctValues('channel');
    }

    /**
     * Lists distinct audit actions available for the current tenant.
     *
     * Responsibility: Lists distinct audit actions available for the current tenant.
     * @return string[]
     */
    public function distinctActions(): array
    {
        return $this->distinctValues('action');
    }

    /**
     * Lists distinct audited resources available for the current tenant.
     *
     * Responsibility: Lists distinct audited resources available for the current tenant.
     * @return string[]
     */
    public function distinctResources(): array
    {
        return $this->distinctValues('resource');
    }

    /**
     * Reads distinct non-empty values from a whitelisted audit column.
     *
     * Responsibility: Reads distinct non-empty values from a whitelisted audit column.
     * @return string[]
     */
    private function distinctValues(string $column): array
    {
        try {
            $rows = $this->db->connection()->select(
                'SELECT DISTINCT ' . $column . ' AS value
                 FROM audit_logs
                 WHERE tenant_id = ?
                   AND ' . $column . ' IS NOT NULL
                   AND ' . $column . " <> '' ORDER BY " . $column,
                [$this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('AuditLogRepository::distinctValues failed', [
                'column' => $column,
                'error' => $e->getMessage(),
            ]);
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (array $row): string => trim((string) ($row['value'] ?? '')),
            $rows
        )));
    }

    /**
     * Restricts requested sort columns to safe audit log fields.
     *
     * Responsibility: Restricts requested sort columns to safe audit log fields.
     */
    private function resolveSortColumn(string $sort): string
    {
        return match ($sort) {
            'id', 'channel', 'action', 'resource', 'actor_id', 'occurred_at' => $sort,
            default => 'occurred_at',
        };
    }

    /**
     * Normalizes requested sort direction to SQL ASC or DESC.
     *
     * Responsibility: Normalizes requested sort direction to SQL ASC or DESC.
     */
    private function resolveSortDirection(string $direction): string
    {
        return strtolower($direction) === 'asc' ? 'ASC' : 'DESC';
    }

    /**
     * Resolves the required tenant identifier for audit queries.
     *
     * Responsibility: Resolves the required tenant identifier for audit queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
