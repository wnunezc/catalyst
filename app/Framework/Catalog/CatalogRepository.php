<?php

declare(strict_types=1);

namespace Catalyst\Framework\Catalog;

use Catalyst\Entities\CatalogDefinition;
use Catalyst\Entities\CatalogItem;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

final class CatalogRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;
    private CatalogItemAvailabilityDecorator $itemDecorator;
    private CatalogOptionMapBuilder $optionMapBuilder;

    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
        $this->itemDecorator = new CatalogItemAvailabilityDecorator();
        $this->optionMapBuilder = new CatalogOptionMapBuilder();
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function searchDefinitions(array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 15));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $state = trim((string) ($criteria['state'] ?? ''));
        $sort = $this->resolveDefinitionSort((string) ($criteria['sort'] ?? 'updated_at'));
        $direction = $this->resolveSortDirection((string) ($criteria['direction'] ?? 'desc'));

        $where = ['cd.tenant_id = ?'];
        $bindings = [$this->currentTenantId()];

        if ($search !== '') {
            $needle = '%' . $search . '%';
            $where[] = '(cd.catalog_key LIKE ? OR cd.label LIKE ? OR COALESCE(cd.description, \'\') LIKE ?)';
            array_push($bindings, $needle, $needle, $needle);
        }

        if ($state !== '') {
            $where[] = 'COALESCE(wi.current_state, ?) = ?';
            array_push($bindings, 'draft', $state);
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate
                 FROM catalog_definitions cd
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = cd.id
                   AND wi.tenant_id = cd.tenant_id'
                . $whereSql,
                array_merge([CatalogManager::RESOURCE_KEY], $bindings)
            );

            $rows = $this->db->connection()->select(
                'SELECT cd.id, cd.catalog_key, cd.label, cd.description, cd.lock_version, cd.created_at, cd.updated_at,
                        COALESCE(wi.current_state, ?) AS current_state,
                        COUNT(ci.id) AS item_count,
                        SUM(CASE WHEN ci.is_enabled = 1 THEN 1 ELSE 0 END) AS enabled_item_count
                 FROM catalog_definitions cd
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = cd.id
                   AND wi.tenant_id = cd.tenant_id
                 LEFT JOIN catalog_items ci
                    ON ci.catalog_definition_id = cd.id
                   AND ci.tenant_id = cd.tenant_id'
                . $whereSql
                . ' GROUP BY cd.id, cd.catalog_key, cd.label, cd.description, cd.lock_version, cd.created_at, cd.updated_at, wi.current_state
                    ORDER BY ' . $sort . ' ' . $direction . ', cd.label ASC
                    LIMIT ? OFFSET ?',
                array_merge(['draft', CatalogManager::RESOURCE_KEY], $bindings, [$perPage, $offset])
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::searchDefinitions failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }

        return [
            'rows' => array_map([$this, 'normalizeDefinitionRow'], $rows),
            'total' => (int) ($totalRow['aggregate'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDefinition(int $id): ?array
    {
        $result = $this->searchDefinitions([
            'page' => 1,
            'per_page' => 1,
            'search' => '',
            'state' => '',
            'sort' => 'updated_at',
            'direction' => 'desc',
        ]);

        foreach ($result['rows'] as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        try {
            $row = $this->db->connection()->selectOne(
                'SELECT cd.id, cd.catalog_key, cd.label, cd.description, cd.lock_version, cd.created_at, cd.updated_at,
                        COALESCE(wi.current_state, ?) AS current_state
                 FROM catalog_definitions cd
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = cd.id
                   AND wi.tenant_id = cd.tenant_id
                 WHERE cd.id = ?
                   AND cd.tenant_id = ?',
                ['draft', CatalogManager::RESOURCE_KEY, $id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::findDefinition failed', ['id' => $id, 'error' => $e->getMessage()]);

            return null;
        }

        if (!is_array($row)) {
            return null;
        }

        $normalized = $this->normalizeDefinitionRow($row);
        $normalized['items'] = $this->itemsForCatalog($id, true);
        $normalized['version_count'] = $this->versionCount($id);

        return $normalized;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDefinitionByKey(string $catalogKey): ?array
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT cd.id, cd.catalog_key, cd.label, cd.description, cd.lock_version, cd.created_at, cd.updated_at,
                        COALESCE(wi.current_state, ?) AS current_state
                 FROM catalog_definitions cd
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = cd.id
                   AND wi.tenant_id = cd.tenant_id
                 WHERE cd.catalog_key = ?
                   AND cd.tenant_id = ?
                 LIMIT 1',
                ['draft', CatalogManager::RESOURCE_KEY, trim(strtolower($catalogKey)), $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::findDefinitionByKey failed', ['catalog_key' => $catalogKey, 'error' => $e->getMessage()]);

            return null;
        }

        return is_array($row) ? $this->normalizeDefinitionRow($row) : null;
    }

    public function findDefinitionModel(int $id): ?CatalogDefinition
    {
        return CatalogDefinition::find($id);
    }

    public function findItemModel(int $id): ?CatalogItem
    {
        return CatalogItem::find($id);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findItem(int $catalogId, int $itemId): ?array
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT ci.*, cd.catalog_key
                 FROM catalog_items ci
                 INNER JOIN catalog_definitions cd
                    ON cd.id = ci.catalog_definition_id
                   AND cd.tenant_id = ci.tenant_id
                 WHERE ci.catalog_definition_id = ?
                   AND ci.id = ?
                   AND ci.tenant_id = ?',
                [$catalogId, $itemId, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::findItem failed', ['catalog_id' => $catalogId, 'item_id' => $itemId, 'error' => $e->getMessage()]);

            return null;
        }

        return is_array($row) ? $this->normalizeItemRow($row) : null;
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function searchItems(int $catalogId, array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 15));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $state = trim((string) ($criteria['state'] ?? ''));
        $enabled = trim((string) ($criteria['enabled'] ?? ''));
        $sort = $this->resolveItemSort((string) ($criteria['sort'] ?? 'sort_order'));
        $direction = $this->resolveSortDirection((string) ($criteria['direction'] ?? 'asc'));

        $where = [
            'ci.catalog_definition_id = ?',
            'ci.tenant_id = ?',
        ];
        $bindings = [$catalogId, $this->currentTenantId()];

        if ($search !== '') {
            $needle = '%' . $search . '%';
            $where[] = '(ci.item_key LIKE ? OR ci.label LIKE ? OR COALESCE(ci.description, \'\') LIKE ?)';
            array_push($bindings, $needle, $needle, $needle);
        }

        if ($enabled !== '') {
            $where[] = 'ci.is_enabled = ?';
            $bindings[] = in_array(strtolower($enabled), ['1', 'true', 'on', 'yes'], true) ? 1 : 0;
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM catalog_items ci' . $whereSql,
                $bindings
            );

            $rows = $this->db->connection()->select(
                'SELECT ci.*, cd.catalog_key
                 FROM catalog_items ci
                 INNER JOIN catalog_definitions cd
                    ON cd.id = ci.catalog_definition_id
                   AND cd.tenant_id = ci.tenant_id'
                . $whereSql
                . ' ORDER BY ' . $sort . ' ' . $direction . ', ci.label ASC
                    LIMIT ? OFFSET ?',
                array_merge($bindings, [$perPage, $offset])
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::searchItems failed', ['catalog_id' => $catalogId, 'error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }

        $normalized = array_map([$this, 'normalizeItemRow'], $rows);
        if ($state !== '') {
            $normalized = array_values(array_filter(
                $normalized,
                static fn (array $row): bool => (string) ($row['temporal_state'] ?? '') === $state
            ));
        }

        return [
            'rows' => $normalized,
            'total' => $state === '' ? (int) ($totalRow['aggregate'] ?? 0) : count($normalized),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function itemsForCatalog(int $catalogId, bool $includeInactive = false): array
    {
        try {
            $rows = $this->db->connection()->select(
                'SELECT ci.*, cd.catalog_key
                 FROM catalog_items ci
                 INNER JOIN catalog_definitions cd
                    ON cd.id = ci.catalog_definition_id
                   AND cd.tenant_id = ci.tenant_id
                 WHERE ci.catalog_definition_id = ?
                   AND ci.tenant_id = ?
                 ORDER BY ci.sort_order ASC, ci.label ASC',
                [$catalogId, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::itemsForCatalog failed', ['catalog_id' => $catalogId, 'error' => $e->getMessage()]);

            return [];
        }

        $normalized = array_map([$this, 'normalizeItemRow'], $rows);
        if ($includeInactive) {
            return $normalized;
        }

        return array_values(array_filter(
            $normalized,
            static fn (array $row): bool => !empty($row['is_available'])
        ));
    }

    /**
     * @param string[] $selectedKeys
     * @return array<string, string>
     */
    public function optionMap(string $catalogKey, array $selectedKeys = []): array
    {
        $definition = $this->findDefinitionByKey($catalogKey);
        if ($definition === null) {
            return [];
        }

        $rows = $this->itemsForCatalog((int) ($definition['id'] ?? 0), true);
        return $this->optionMapBuilder->buildItemOptions($definition, $rows, $selectedKeys);
    }

    /**
     * @return array<string, string>
     */
    public function definitionOptionMap(bool $includeState = true): array
    {
        try {
            $rows = $this->db->connection()->select(
                'SELECT cd.catalog_key, cd.label, COALESCE(wi.current_state, ?) AS current_state
                 FROM catalog_definitions cd
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = cd.id
                   AND wi.tenant_id = cd.tenant_id
                 WHERE cd.tenant_id = ?
                 ORDER BY cd.label ASC',
                ['draft', CatalogManager::RESOURCE_KEY, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::definitionOptionMap failed', ['error' => $e->getMessage()]);

            return [];
        }

        return $this->optionMapBuilder->buildDefinitionOptions($rows, $includeState);
    }

    public function existsCatalogKey(string $catalogKey, ?int $ignoreId = null): bool
    {
        $bindings = [$this->currentTenantId(), trim(strtolower($catalogKey))];
        $sql = 'SELECT 1
                FROM catalog_definitions
                WHERE tenant_id = ?
                  AND catalog_key = ?';

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> ?';
            $bindings[] = $ignoreId;
        }

        $sql .= ' LIMIT 1';

        try {
            return $this->db->connection()->selectOne($sql, $bindings) !== null;
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::existsCatalogKey failed', ['catalog_key' => $catalogKey, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function existsItemKey(int $catalogId, string $itemKey, ?int $ignoreId = null): bool
    {
        $bindings = [$this->currentTenantId(), $catalogId, trim(strtolower($itemKey))];
        $sql = 'SELECT 1
                FROM catalog_items
                WHERE tenant_id = ?
                  AND catalog_definition_id = ?
                  AND item_key = ?';

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> ?';
            $bindings[] = $ignoreId;
        }

        $sql .= ' LIMIT 1';

        try {
            return $this->db->connection()->selectOne($sql, $bindings) !== null;
        } catch (Exception $e) {
            $this->logger->warning('CatalogRepository::existsItemKey failed', ['catalog_id' => $catalogId, 'item_key' => $itemKey, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function deleteDefinition(CatalogDefinition $definition): void
    {
        $definition->delete();
    }

    public function deleteItem(CatalogItem $item): void
    {
        $item->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshotDefinition(int $catalogId): array
    {
        $definition = $this->findDefinition($catalogId);
        if ($definition === null) {
            return [];
        }

        return [
            'catalog' => $definition,
            'items' => $this->itemsForCatalog($catalogId, true),
        ];
    }

    private function versionCount(int $catalogId): int
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate
                 FROM content_versions
                 WHERE resource_key = ?
                   AND record_id = ?
                   AND tenant_id = ?',
                [CatalogManager::RESOURCE_KEY, $catalogId, $this->currentTenantId()]
            );
        } catch (Exception) {
            return 0;
        }

        return (int) ($row['aggregate'] ?? 0);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeDefinitionRow(array $row): array
    {
        $row['catalog_key'] = trim(strtolower((string) ($row['catalog_key'] ?? '')));
        $row['item_count'] = (int) ($row['item_count'] ?? 0);
        $row['enabled_item_count'] = (int) ($row['enabled_item_count'] ?? 0);
        $row['lock_version'] = (int) ($row['lock_version'] ?? 1);

        return $row;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeItemRow(array $row): array
    {
        return $this->itemDecorator->decorate($row);
    }

    private function resolveDefinitionSort(string $sort): string
    {
        return match ($sort) {
            'catalog_key' => 'cd.catalog_key',
            'label' => 'cd.label',
            'item_count' => 'item_count',
            'updated_at' => 'cd.updated_at',
            'created_at' => 'cd.created_at',
            default => 'cd.updated_at',
        };
    }

    private function resolveItemSort(string $sort): string
    {
        return match ($sort) {
            'item_key' => 'ci.item_key',
            'label' => 'ci.label',
            'sort_order' => 'ci.sort_order',
            'valid_from' => 'ci.valid_from',
            'valid_to' => 'ci.valid_to',
            'updated_at' => 'ci.updated_at',
            default => 'ci.sort_order',
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
