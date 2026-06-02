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

namespace Catalyst\Framework\Metadata;

use Catalyst\Entities\MetadataFieldDefinition;
use Catalyst\Entities\MetadataFieldValue;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Defines the Metadata Field Repository class contract.
 *
 * @package Catalyst\Framework\Metadata
 * Responsibility: Coordinates the metadata field repository behavior within its module boundary.
 */
final class MetadataFieldRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes the Metadata Field Repository instance.
     */
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
        $perPage = max(1, (int) ($criteria['per_page'] ?? 15));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $resourceKey = trim((string) ($criteria['resource_key'] ?? ''));
        $type = trim((string) ($criteria['type'] ?? ''));
        $sort = $this->resolveSortColumn((string) ($criteria['sort'] ?? 'sort_order'));
        $direction = $this->resolveSortDirection((string) ($criteria['direction'] ?? 'asc'));

        $where = [];
        $bindings = [];
        $where[] = 'tenant_id = ?';
        $bindings[] = $this->currentTenantId();

        if ($search !== '') {
            $where[] = '(label LIKE ? OR field_key LIKE ? OR COALESCE(help_text, \'\') LIKE ?)';
            $needle = '%' . $search . '%';
            array_push($bindings, $needle, $needle, $needle);
        }

        if ($resourceKey !== '') {
            $where[] = 'resource_key = ?';
            $bindings[] = $resourceKey;
        }

        if ($type !== '') {
            $where[] = 'type = ?';
            $bindings[] = $type;
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM metadata_field_definitions' . $whereSql,
                $bindings
            );

            $rows = $this->db->connection()->select(
                'SELECT id, resource_key, field_key, label, type, section_key, help_text, placeholder, default_value, options_json, catalog_key, rules_extra, is_required, is_filterable, is_listed, sort_order, max_length, min_value, max_value, created_at, updated_at
                 FROM metadata_field_definitions'
                . $whereSql
                . ' ORDER BY ' . $sort . ' ' . $direction . ', label ASC'
                . ' LIMIT ? OFFSET ?',
                array_merge($bindings, [$perPage, $offset])
            ) ?: [];

            return [
                'rows' => array_map([$this, 'hydrateRow'], $rows),
                'total' => (int) ($totalRow['aggregate'] ?? 0),
            ];
        } catch (Exception $e) {
            $this->logger->warning('MetadataFieldRepository::search failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activeForResource(string $resourceKey): array
    {
        try {
            $rows = $this->db->connection()->select(
                'SELECT id, resource_key, field_key, label, type, section_key, help_text, placeholder, default_value, options_json, catalog_key, rules_extra, is_required, is_filterable, is_listed, sort_order, max_length, min_value, max_value
                 FROM metadata_field_definitions
                 WHERE resource_key = ?
                   AND tenant_id = ?
                 ORDER BY sort_order ASC, label ASC',
                [trim(strtolower($resourceKey)), $this->currentTenantId()]
            ) ?: [];

            return array_map([$this, 'hydrateRow'], $rows);
        } catch (Exception $e) {
            $this->logger->warning('MetadataFieldRepository::activeForResource failed', [
                'resource_key' => $resourceKey,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT id, resource_key, field_key, label, type, section_key, help_text, placeholder, default_value, options_json, catalog_key, rules_extra, is_required, is_filterable, is_listed, sort_order, max_length, min_value, max_value
                 FROM metadata_field_definitions
                 WHERE id = ?
                   AND tenant_id = ?',
                [$id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('MetadataFieldRepository::find failed', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        return $row === null ? null : $this->hydrateRow($row);
    }

    /**
     * Finds the requested record.
     */
    public function findModel(int $id): ?MetadataFieldDefinition
    {
        return MetadataFieldDefinition::find($id);
    }

    /**
     * Handles the exists field key workflow.
     */
    public function existsFieldKey(string $resourceKey, string $fieldKey, ?int $ignoreId = null): bool
    {
        $bindings = [$this->currentTenantId(), trim(strtolower($resourceKey)), trim(strtolower($fieldKey))];
        $sql = 'SELECT 1 FROM metadata_field_definitions WHERE tenant_id = ? AND resource_key = ? AND field_key = ?';

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> ?';
            $bindings[] = $ignoreId;
        }

        $sql .= ' LIMIT 1';

        try {
            return $this->db->connection()->selectOne($sql, $bindings) !== null;
        } catch (Exception $e) {
            $this->logger->warning('MetadataFieldRepository::existsFieldKey failed', [
                'resource_key' => $resourceKey,
                'field_key' => $fieldKey,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function persist(array $payload, ?MetadataFieldDefinition $definition = null): MetadataFieldDefinition
    {
        $definition ??= new MetadataFieldDefinition();
        $definition->fill($payload);
        $definition->save();

        return $definition;
    }

    /**
     * Handles the delete workflow.
     */
    public function delete(MetadataFieldDefinition $definition): void
    {
        $values = MetadataFieldValue::query()
            ->whereEqual('field_definition_id', (int) $definition->getKey())
            ->get();

        foreach ($values as $value) {
            if ($value instanceof MetadataFieldValue) {
                $value->delete();
            }
        }

        $definition->delete();
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function hydrateRow(array $row): array
    {
        $decodedOptions = [];

        if (isset($row['options_json']) && is_string($row['options_json']) && $row['options_json'] !== '') {
            $decoded = json_decode($row['options_json'], true);
            $decodedOptions = is_array($decoded) ? $decoded : [];
        } elseif (isset($row['options_json']) && is_array($row['options_json'])) {
            $decodedOptions = $row['options_json'];
        }

        $row['resource_key'] = trim(strtolower((string) ($row['resource_key'] ?? '')));
        $row['field_key'] = trim(strtolower((string) ($row['field_key'] ?? '')));
        $row['type'] = trim(strtolower((string) ($row['type'] ?? 'text')));
        $row['catalog_key'] = $this->nullableLowerString($row['catalog_key'] ?? null);
        $row['is_required'] = (bool) ($row['is_required'] ?? false);
        $row['is_filterable'] = (bool) ($row['is_filterable'] ?? false);
        $row['is_listed'] = (bool) ($row['is_listed'] ?? false);
        $row['sort_order'] = (int) ($row['sort_order'] ?? 0);
        $row['max_length'] = $row['max_length'] !== null ? (int) $row['max_length'] : null;
        $row['min_value'] = $row['min_value'] !== null ? (float) $row['min_value'] : null;
        $row['max_value'] = $row['max_value'] !== null ? (float) $row['max_value'] : null;
        $row['options_json'] = $decodedOptions;

        return $row;
    }

    /**
     * Resolves the requested value.
     */
    private function resolveSortColumn(string $sort): string
    {
        return match ($sort) {
            'id', 'resource_key', 'field_key', 'label', 'type', 'sort_order', 'created_at', 'updated_at' => $sort,
            default => 'sort_order',
        };
    }

    /**
     * Resolves the requested value.
     */
    private function resolveSortDirection(string $direction): string
    {
        return strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
    }

    /**
     * Handles the nullable lower string workflow.
     */
    private function nullableLowerString(mixed $value): ?string
    {
        $value = trim(strtolower((string) ($value ?? '')));

        return $value === '' ? null : $value;
    }

    /**
     * Handles the current tenant id workflow.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
