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

namespace Catalyst\Framework\Catalog;

use Catalyst\Entities\CatalogDefinition;
use Catalyst\Entities\CatalogItem;
use Catalyst\Entities\WorkflowInstance;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Temporal\EffectiveWindow;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;
use RuntimeException;

/**
 * Orchestrates catalog lifecycle operations, versioning and workflow transitions.
 *
 * @package Catalyst\Framework\Catalog
 * Responsibility: Applies catalog mutations and coordinates persistence, temporal rules and versions.
 */
final class CatalogManager
{
    use SingletonTrait;

    public const RESOURCE_KEY = 'catalogs';
    public const WORKFLOW_KEY = 'catalogs.lifecycle';

    private CatalogRepository $repository;
    private WorkflowManager $workflows;
    private WorkflowRepository $workflowRepository;
    private VersionManager $versions;
    private DatabaseManager $db;
    private EffectiveWindow $window;

    /**
     * Initializes the Catalog Manager instance.
     *
     * Responsibility: Initializes the Catalog Manager instance.
     */
    protected function __construct()
    {
        $this->repository = CatalogRepository::getInstance();
        $this->workflows = WorkflowManager::getInstance();
        $this->workflowRepository = WorkflowRepository::getInstance();
        $this->versions = VersionManager::getInstance();
        $this->db = DatabaseManager::getInstance();
        $this->window = EffectiveWindow::getInstance();
    }

    /**
     * Creates a catalog definition and initializes its workflow history.
     *
     * Responsibility: Creates a catalog definition and initializes its workflow history.
     * @param array<string, mixed> $payload
     */
    public function createCatalog(array $payload): CatalogDefinition
    {
        $catalog = CatalogDefinition::create([
            'catalog_key' => trim(strtolower((string) ($payload['catalog_key'] ?? ''))),
            'label' => trim((string) ($payload['label'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
        ]);

        $this->workflows->ensureInstance(self::WORKFLOW_KEY, self::RESOURCE_KEY, (int) $catalog->getKey());
        $this->captureCatalogVersion((int) $catalog->getKey(), 'Catalog created');

        return $catalog;
    }

    /**
     * Updates a catalog definition and captures the resulting version.
     *
     * Responsibility: Updates a catalog definition and captures the resulting version.
     * @param array<string, mixed> $payload
     */
    public function updateCatalog(CatalogDefinition $catalog, array $payload): CatalogDefinition
    {
        $catalog->fill([
            'catalog_key' => trim(strtolower((string) ($payload['catalog_key'] ?? ''))),
            'label' => trim((string) ($payload['label'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
        ]);
        $catalog->save();

        $this->captureCatalogVersion((int) $catalog->getKey(), 'Catalog updated');

        return $catalog;
    }

    /**
     * Deletes a catalog definition.
     *
     * Responsibility: Deletes a catalog definition.
     */
    public function deleteCatalog(CatalogDefinition $catalog): void
    {
        $this->repository->deleteDefinition($catalog);
    }

    /**
     * Applies a lifecycle transition to a catalog definition.
     *
     * Responsibility: Applies a lifecycle transition to a catalog definition.
     */
    public function transitionCatalog(CatalogDefinition $catalog, string $transitionKey, ?string $notes = null): array
    {
        $result = $this->workflows->transition(
            self::WORKFLOW_KEY,
            self::RESOURCE_KEY,
            (int) $catalog->getKey(),
            $transitionKey,
            record: $catalog,
            notes: $notes
        );

        $this->captureCatalogVersion((int) $catalog->getKey(), 'Catalog workflow transitioned: ' . $transitionKey);

        return $result;
    }

    /**
     * Creates an item inside an existing catalog.
     *
     * Responsibility: Creates an item inside an existing catalog.
     * @param array<string, mixed> $payload
     */
    public function createItem(int $catalogId, array $payload): CatalogItem
    {
        $catalog = $this->requireCatalog($catalogId);

        $item = CatalogItem::create([
            'catalog_definition_id' => (int) $catalog->getKey(),
            'item_key' => trim(strtolower((string) ($payload['item_key'] ?? ''))),
            'label' => trim((string) ($payload['label'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'is_enabled' => filter_var($payload['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'valid_from' => $this->window->normalize(isset($payload['valid_from']) ? (string) $payload['valid_from'] : null),
            'valid_to' => $this->window->normalize(isset($payload['valid_to']) ? (string) $payload['valid_to'] : null),
            'sort_order' => max(0, (int) ($payload['sort_order'] ?? 100)),
            'metadata_json' => $this->decodeJsonField($payload['metadata_json'] ?? []),
        ]);

        $this->captureCatalogVersion((int) $catalog->getKey(), 'Catalog item created: ' . (string) ($payload['item_key'] ?? 'item'));

        return $item;
    }

    /**
     * Updates an existing catalog item.
     *
     * Responsibility: Updates an existing catalog item.
     * @param array<string, mixed> $payload
     */
    public function updateItem(CatalogItem $item, array $payload): CatalogItem
    {
        $catalogId = (int) ($item->toArray()['catalog_definition_id'] ?? 0);
        $item->fill([
            'item_key' => trim(strtolower((string) ($payload['item_key'] ?? ''))),
            'label' => trim((string) ($payload['label'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'is_enabled' => filter_var($payload['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'valid_from' => $this->window->normalize(isset($payload['valid_from']) ? (string) $payload['valid_from'] : null),
            'valid_to' => $this->window->normalize(isset($payload['valid_to']) ? (string) $payload['valid_to'] : null),
            'sort_order' => max(0, (int) ($payload['sort_order'] ?? 100)),
            'metadata_json' => $this->decodeJsonField($payload['metadata_json'] ?? []),
        ]);
        $item->save();

        $this->captureCatalogVersion($catalogId, 'Catalog item updated: ' . (string) ($payload['item_key'] ?? 'item'));

        return $item;
    }

    /**
     * Deletes an item and records the catalog version.
     *
     * Responsibility: Deletes an item and records the catalog version.
     */
    public function deleteItem(CatalogItem $item): void
    {
        $catalogId = (int) ($item->toArray()['catalog_definition_id'] ?? 0);
        $itemKey = (string) ($item->toArray()['item_key'] ?? 'item');
        $this->repository->deleteItem($item);
        $this->captureCatalogVersion($catalogId, 'Catalog item deleted: ' . $itemKey);
    }

    /**
     * Returns the selectable options for a catalog key.
     *
     * Responsibility: Returns the selectable options for a catalog key.
     * @param string[] $selectedKeys
     * @return array<string, string>
     */
    public function options(string $catalogKey, array $selectedKeys = []): array
    {
        return $this->repository->optionMap($catalogKey, $selectedKeys);
    }

    /**
     * Determines whether a catalog exposes an item key as an available option.
     *
     * Responsibility: Determines whether a catalog exposes an item key as an available option.
     */
    public function hasOption(string $catalogKey, string $itemKey): bool
    {
        return array_key_exists(trim(strtolower($itemKey)), $this->options($catalogKey));
    }

    /**
     * Returns a complete snapshot of a catalog and its items.
     *
     * Responsibility: Returns a complete snapshot of a catalog and its items.
     * @return array<string, mixed>
     */
    public function snapshotCatalog(int $catalogId): array
    {
        $snapshot = $this->repository->snapshotDefinition($catalogId);
        if ($snapshot === []) {
            throw new RuntimeException('Catalog snapshot could not be resolved.');
        }

        return $snapshot;
    }

    /**
     * Restores a catalog and its items from a captured snapshot.
     *
     * Responsibility: Restores a catalog and its items from a captured snapshot.
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    public function restoreCatalogSnapshot(int $catalogId, array $snapshot): array
    {
        $catalog = $this->requireCatalog($catalogId);
        $catalogPayload = is_array($snapshot['catalog'] ?? null) ? (array) $snapshot['catalog'] : [];
        $itemPayloads = is_array($snapshot['items'] ?? null) ? (array) $snapshot['items'] : [];

        $this->db->connection()->transaction(function () use ($catalog, $catalogPayload, $itemPayloads): void {
            $definitionData = $this->sanitizeCatalogSnapshot($catalogPayload);
            $catalog->fill($definitionData);
            $catalog->save();

            foreach ($this->repository->itemsForCatalog((int) $catalog->getKey(), true) as $row) {
                $model = $this->repository->findItemModel((int) ($row['id'] ?? 0));
                if ($model instanceof CatalogItem) {
                    $model->delete();
                }
            }

            foreach ($itemPayloads as $row) {
                if (!is_array($row)) {
                    continue;
                }

                CatalogItem::create(array_merge(
                    ['catalog_definition_id' => (int) $catalog->getKey()],
                    $this->sanitizeItemSnapshot($row)
                ));
            }

            $instanceData = $this->workflows->ensureInstance(self::WORKFLOW_KEY, self::RESOURCE_KEY, (int) $catalog->getKey());
            $instance = $this->workflowRepository->findModel((int) ($instanceData['id'] ?? 0));
            if ($instance instanceof WorkflowInstance) {
                $state = trim((string) ($catalogPayload['current_state'] ?? 'draft'));
                $this->workflowRepository->updateState($instance, $state === '' ? 'draft' : $state, [
                    'restored_from_version' => true,
                ]);
            }
        });

        $restored = $this->repository->findDefinition((int) $catalog->getKey());
        if ($restored === null) {
            throw new RuntimeException('Restored catalog could not be reloaded.');
        }

        return $restored;
    }

    /**
     * Captures the current catalog snapshot as a content version.
     *
     * Responsibility: Captures the current catalog snapshot as a content version.
     */
    private function captureCatalogVersion(int $catalogId, string $summary): void
    {
        if ($catalogId <= 0) {
            return;
        }

        $this->versions->capture(
            self::RESOURCE_KEY,
            $catalogId,
            $this->snapshotCatalog($catalogId),
            $summary
        );
    }

    /**
     * Returns an existing catalog definition or fails explicitly.
     *
     * Responsibility: Returns an existing catalog definition or fails explicitly.
     */
    private function requireCatalog(int $catalogId): CatalogDefinition
    {
        $catalog = CatalogDefinition::find($catalogId);
        if (!$catalog instanceof CatalogDefinition) {
            throw new RuntimeException('Catalog not found.');
        }

        return $catalog;
    }

    /**
     * Decodes a JSON-compatible field into an array.
     *
     * Responsibility: Decodes a JSON-compatible field into an array.
     * @param mixed $value
     * @return array<string, mixed>|array<int, mixed>
     */
    private function decodeJsonField(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Keeps only restorable fields from a catalog snapshot.
     *
     * Responsibility: Keeps only restorable fields from a catalog snapshot.
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sanitizeCatalogSnapshot(array $payload): array
    {
        return [
            'catalog_key' => trim(strtolower((string) ($payload['catalog_key'] ?? ''))),
            'label' => trim((string) ($payload['label'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'lock_version' => max(1, (int) ($payload['lock_version'] ?? 1)),
        ];
    }

    /**
     * Keeps only restorable fields from a catalog-item snapshot.
     *
     * Responsibility: Keeps only restorable fields from a catalog-item snapshot.
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sanitizeItemSnapshot(array $payload): array
    {
        return [
            'item_key' => trim(strtolower((string) ($payload['item_key'] ?? ''))),
            'label' => trim((string) ($payload['label'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'is_enabled' => !empty($payload['is_enabled']),
            'valid_from' => $this->window->normalize(isset($payload['valid_from']) ? (string) $payload['valid_from'] : null),
            'valid_to' => $this->window->normalize(isset($payload['valid_to']) ? (string) $payload['valid_to'] : null),
            'sort_order' => max(0, (int) ($payload['sort_order'] ?? 100)),
            'metadata_json' => is_array($payload['metadata_json'] ?? null) ? (array) $payload['metadata_json'] : [],
            'lock_version' => max(1, (int) ($payload['lock_version'] ?? 1)),
        ];
    }
}
