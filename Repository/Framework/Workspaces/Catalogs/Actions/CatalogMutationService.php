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

namespace Catalyst\Repository\Workspaces\Catalogs\Actions;

use Catalyst\Entities\CatalogDefinition;
use Catalyst\Entities\CatalogItem;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Helpers\Log\Logger;
use RuntimeException;
use Throwable;

/**
 * Applies claim-protected mutations to catalogs and their items.
 *
 * @package Catalyst\Repository\Workspaces\Catalogs\Actions
 * Responsibility: Update, delete, transition and restore catalog data while enforcing parent catalog claims.
 */
final class CatalogMutationService
{
    private Logger $logger;

    /**
     * Initializes the Catalog Mutation Service instance.
     *
     * Responsibility: Initializes the Catalog Mutation Service instance.
     */
    public function __construct(
        private readonly CatalogManager $manager
    ) {
        $this->logger = Logger::getInstance();
    }

    /**
     * Updates a catalog definition and releases its record claim after persistence.
     *
     * Responsibility: Updates a catalog definition and releases its record claim after persistence.
     * @param array<string, mixed> $payload
     */
    public function updateCatalog(CatalogDefinition $catalog, Request $request, array $payload): void
    {
        $catalogId = (int) $catalog->getKey();
        $this->assertClaim($catalogId, $request);

        $catalog->fill([
            'lock_version' => max(1, (int) $request->input('lock_version', $catalog->toArray()['lock_version'] ?? 1)),
        ]);

        $this->manager->updateCatalog($catalog, $payload);
        $this->releaseClaim($catalogId, $request, 'catalog updated');
    }

    /**
     * Deletes a catalog definition and releases its record claim.
     *
     * Responsibility: Deletes a catalog definition and releases its record claim.
     */
    public function deleteCatalog(CatalogDefinition $catalog, Request $request): void
    {
        $catalogId = (int) $catalog->getKey();
        $this->assertClaim($catalogId, $request);
        $this->manager->deleteCatalog($catalog);
        $this->releaseClaim($catalogId, $request, 'catalog deleted');
    }

    /**
     * Applies a workflow transition to a catalog definition after claim validation.
     *
     * Responsibility: Applies a workflow transition to a catalog definition after claim validation.
     */
    public function transitionCatalog(CatalogDefinition $catalog, Request $request, string $transition, ?string $notes): void
    {
        $catalogId = (int) $catalog->getKey();
        $this->assertClaim($catalogId, $request);
        $this->manager->transitionCatalog($catalog, $transition, $notes);
    }

    /**
     * Restores a captured catalog definition version after claim validation.
     *
     * Responsibility: Restores a captured catalog definition version after claim validation.
     */
    public function restoreCatalogVersion(CatalogDefinition $catalog, Request $request, int $versionId): void
    {
        $catalogId = (int) $catalog->getKey();
        $this->assertClaim($catalogId, $request);

        $versionManager = VersionManager::getInstance();
        $restored = $versionManager->restore($versionId);
        $versionManager->capture(
            CatalogManager::RESOURCE_KEY,
            $catalogId,
            $restored,
            __('catalogs.messages.catalog_restored_summary') . ' ' . $versionId
        );
    }

    /**
     * Creates an item within a claimed catalog definition.
     *
     * Responsibility: Creates an item within a claimed catalog definition.
     * @param array<string, mixed> $payload
     */
    public function createItem(int $catalogId, Request $request, array $payload): void
    {
        $this->assertClaim($catalogId, $request);
        $this->manager->createItem($catalogId, $payload);
        $this->releaseClaim($catalogId, $request, 'catalog item created');
    }

    /**
     * Updates an item within a claimed catalog definition.
     *
     * Responsibility: Updates an item within a claimed catalog definition.
     * @param array<string, mixed> $payload
     */
    public function updateItem(int $catalogId, CatalogItem $item, Request $request, array $payload): void
    {
        $this->assertClaim($catalogId, $request);

        $item->fill([
            'lock_version' => max(1, (int) $request->input('lock_version', $item->toArray()['lock_version'] ?? 1)),
        ]);

        $this->manager->updateItem($item, $payload);
        $this->releaseClaim($catalogId, $request, 'catalog item updated');
    }

    /**
     * Deletes an item from a claimed catalog definition.
     *
     * Responsibility: Deletes an item from a claimed catalog definition.
     */
    public function deleteItem(int $catalogId, CatalogItem $item, Request $request): void
    {
        $this->assertClaim($catalogId, $request);
        $this->manager->deleteItem($item);
        $this->releaseClaim($catalogId, $request, 'catalog item deleted');
    }

    /**
     * Verifies that the request holds an available claim for the parent catalog.
     *
     * Responsibility: Verifies that the request holds an available claim for the parent catalog.
     */
    private function assertClaim(int $catalogId, Request $request): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        RecordClaimManager::getInstance()->assertAvailable(
            resourceKey: CatalogManager::RESOURCE_KEY,
            recordId: $catalogId,
            claimToken: $claimToken !== '' ? $claimToken : null
        );
    }

    /**
     * Releases the parent catalog claim after a successful mutation without masking persistence success.
     *
     * Responsibility: Releases the parent catalog claim after a successful mutation without masking persistence success.
     */
    private function releaseClaim(int $catalogId, Request $request, ?string $reason = null): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        try {
            RecordClaimManager::getInstance()->release(
                resourceKey: CatalogManager::RESOURCE_KEY,
                recordId: $catalogId,
                reason: $reason,
                claimToken: $claimToken !== '' ? $claimToken : null
            );
        } catch (Throwable $e) {
            $this->logger->warning('Catalog claim release skipped after mutation.', [
                'record_id' => $catalogId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
