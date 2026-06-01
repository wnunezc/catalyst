<?php

declare(strict_types=1);

namespace Catalyst\Framework\Audit;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

final class AuditLogRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
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
     * @return string[]
     */
    public function distinctChannels(): array
    {
        return $this->distinctValues('channel');
    }

    /**
     * @return string[]
     */
    public function distinctActions(): array
    {
        return $this->distinctValues('action');
    }

    /**
     * @return string[]
     */
    public function distinctResources(): array
    {
        return $this->distinctValues('resource');
    }

    /**
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

    private function resolveSortColumn(string $sort): string
    {
        return match ($sort) {
            'id', 'channel', 'action', 'resource', 'actor_id', 'occurred_at' => $sort,
            default => 'occurred_at',
        };
    }

    private function resolveSortDirection(string $direction): string
    {
        return strtolower($direction) === 'asc' ? 'ASC' : 'DESC';
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
