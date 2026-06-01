<?php

declare(strict_types=1);

namespace Catalyst\Framework\Media;

use Catalyst\Entities\MediaItem;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Metadata\MetadataValueRepository;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

final class MediaRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;
    private MetadataValueRepository $values;

    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
        $this->values = MetadataValueRepository::getInstance();
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<int, array<string, mixed>> $definitions
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $criteria, array $definitions): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 15));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $disk = trim((string) ($criteria['disk'] ?? ''));
        $mimeGroup = trim((string) ($criteria['mime_group'] ?? ''));
        $metadataFilters = is_array($criteria['metadata_filters'] ?? null)
            ? $criteria['metadata_filters']
            : [];
        $sort = $this->resolveSortColumn((string) ($criteria['sort'] ?? 'created_at'));
        $direction = $this->resolveSortDirection((string) ($criteria['direction'] ?? 'desc'));

        $where = [];
        $bindings = [];
        $tenantId = $this->currentTenantId();

        $where[] = 'm.tenant_id = ?';
        $bindings[] = $tenantId;

        if ($search !== '') {
            $where[] = '(m.name LIKE ? OR m.original_name LIKE ? OR m.path LIKE ? OR m.mime_type LIKE ?)';
            $needle = '%' . $search . '%';
            array_push($bindings, $needle, $needle, $needle, $needle);
        }

        if ($disk !== '') {
            $where[] = 'm.disk = ?';
            $bindings[] = $disk;
        }

        if ($mimeGroup !== '') {
            $where[] = 'm.mime_type LIKE ?';
            $bindings[] = $mimeGroup . '/%';
        }

        foreach ($definitions as $definition) {
            $fieldKey = (string) ($definition['field_key'] ?? '');
            $filterValue = trim((string) ($metadataFilters[$fieldKey] ?? ''));

            if ($fieldKey === '' || $filterValue === '') {
                continue;
            }

            [$clause, $clauseBindings] = $this->metadataFilterClause($definition, $filterValue);
            if ($clause === '') {
                continue;
            }

            $where[] = $clause;
            $bindings = array_merge($bindings, $clauseBindings);
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM media_library m' . $whereSql,
                $bindings
            );

            $rows = $this->db->connection()->select(
                'SELECT m.id, m.name, m.original_name, m.disk, m.path, m.public_url, m.mime_type, m.extension, m.size_bytes, m.created_at
                 FROM media_library m'
                . $whereSql
                . ' ORDER BY ' . $sort . ' ' . $direction
                . ' LIMIT ? OFFSET ?',
                array_merge($bindings, [$perPage, $offset])
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('MediaRepository::search failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }

        $recordIds = array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $rows);
        $metadataValues = $this->values->valuesForRecords(MediaManager::RESOURCE_KEY, $recordIds, $definitions);

        foreach ($rows as &$row) {
            $rowId = (int) ($row['id'] ?? 0);
            $row['metadata'] = $metadataValues[$rowId] ?? [];
            $row['metadata_display'] = [];

            foreach ((array) ($row['metadata'] ?? []) as $fieldKey => $metadata) {
                $row['metadata_display'][$fieldKey] = (string) ($metadata['display'] ?? '');
            }
        }
        unset($row);

        return [
            'rows' => $rows,
            'total' => (int) ($totalRow['aggregate'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id, array $definitions = []): ?array
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT id, name, original_name, disk, path, public_url, mime_type, extension, size_bytes, created_at, updated_at
                 FROM media_library
                 WHERE id = ?
                   AND tenant_id = ?',
                [$id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('MediaRepository::find failed', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($row === null) {
            return null;
        }

        $row['metadata'] = $definitions === []
            ? []
            : $this->values->valuesForRecord(MediaManager::RESOURCE_KEY, $id, $definitions);

        return $row;
    }

    public function findModel(int $id): ?MediaItem
    {
        return MediaItem::find($id);
    }

    /**
     * @return string[]
     */
    public function distinctMimeGroups(): array
    {
        try {
            $rows = $this->db->connection()->select(
                'SELECT DISTINCT SUBSTRING_INDEX(mime_type, "/", 1) AS mime_group
                 FROM media_library
                 WHERE tenant_id = ?
                   AND mime_type IS NOT NULL
                   AND mime_type <> ""
                 ORDER BY mime_group ASC',
                [$this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('MediaRepository::distinctMimeGroups failed', ['error' => $e->getMessage()]);

            return [];
        }

        return array_values(array_filter(array_map(
            static fn (array $row): string => trim((string) ($row['mime_group'] ?? '')),
            $rows
        )));
    }

    /**
     * @param array<string, mixed> $definition
     * @return array{0: string, 1: array<int, mixed>}
     */
    private function metadataFilterClause(array $definition, string $filterValue): array
    {
        $fieldId = (int) ($definition['id'] ?? 0);
        if ($fieldId <= 0) {
            return ['', []];
        }

        $base = 'EXISTS (
            SELECT 1
            FROM metadata_field_values mv
            WHERE mv.resource_key = ?
              AND mv.tenant_id = ?
              AND mv.record_id = m.id
              AND mv.field_definition_id = ?';
        $bindings = [MediaManager::RESOURCE_KEY, $this->currentTenantId(), $fieldId];
        $type = (string) ($definition['type'] ?? 'text');

        if (in_array($type, ['text', 'textarea'], true)) {
            $base .= ' AND COALESCE(mv.value_text, \'\') LIKE ?)';
            $bindings[] = '%' . $filterValue . '%';

            return [$base, $bindings];
        }

        if ($type === 'number') {
            if (!is_numeric($filterValue)) {
                return ['', []];
            }

            $base .= ' AND mv.value_number = ?)';
            $bindings[] = (float) $filterValue;

            return [$base, $bindings];
        }

        if ($type === 'boolean') {
            $base .= ' AND mv.value_boolean = ?)';
            $bindings[] = filter_var($filterValue, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            return [$base, $bindings];
        }

        if ($type === 'date') {
            $timestamp = strtotime($filterValue);
            if ($timestamp === false) {
                return ['', []];
            }

            $base .= ' AND mv.value_date = ?)';
            $bindings[] = date('Y-m-d', $timestamp);

            return [$base, $bindings];
        }

        if ($type === 'datetime') {
            $timestamp = strtotime($filterValue);
            if ($timestamp === false) {
                return ['', []];
            }

            $base .= ' AND mv.value_datetime = ?)';
            $bindings[] = date('Y-m-d H:i:s', $timestamp);

            return [$base, $bindings];
        }

        if ($type === 'media') {
            if (!ctype_digit($filterValue)) {
                return ['', []];
            }

            $base .= ' AND mv.media_item_id = ?)';
            $bindings[] = (int) $filterValue;

            return [$base, $bindings];
        }

        $base .= ' AND mv.value_text = ?)';
        $bindings[] = $filterValue;

        return [$base, $bindings];
    }

    private function resolveSortColumn(string $sort): string
    {
        return match ($sort) {
            'id' => 'm.id',
            'name' => 'm.name',
            'disk' => 'm.disk',
            'mime_type' => 'm.mime_type',
            'size_bytes' => 'm.size_bytes',
            'created_at' => 'm.created_at',
            default => 'm.created_at',
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
