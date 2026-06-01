<?php

declare(strict_types=1);

namespace Catalyst\Repository\Catalogs\Actions;

use Catalyst\Entities\CatalogDefinition;
use Catalyst\Entities\CatalogItem;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Helpers\Log\Logger;
use RuntimeException;
use Throwable;

final class CatalogMutationService
{
    private Logger $logger;

    public function __construct(
        private readonly CatalogManager $manager
    ) {
        $this->logger = Logger::getInstance();
    }

    /**
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

    public function deleteCatalog(CatalogDefinition $catalog, Request $request): void
    {
        $catalogId = (int) $catalog->getKey();
        $this->assertClaim($catalogId, $request);
        $this->manager->deleteCatalog($catalog);
        $this->releaseClaim($catalogId, $request, 'catalog deleted');
    }

    public function transitionCatalog(CatalogDefinition $catalog, Request $request, string $transition, ?string $notes): void
    {
        $catalogId = (int) $catalog->getKey();
        $this->assertClaim($catalogId, $request);
        $this->manager->transitionCatalog($catalog, $transition, $notes);
    }

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
     * @param array<string, mixed> $payload
     */
    public function createItem(int $catalogId, Request $request, array $payload): void
    {
        $this->assertClaim($catalogId, $request);
        $this->manager->createItem($catalogId, $payload);
        $this->releaseClaim($catalogId, $request, 'catalog item created');
    }

    /**
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

    public function deleteItem(int $catalogId, CatalogItem $item, Request $request): void
    {
        $this->assertClaim($catalogId, $request);
        $this->manager->deleteItem($item);
        $this->releaseClaim($catalogId, $request, 'catalog item deleted');
    }

    private function assertClaim(int $catalogId, Request $request): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        RecordClaimManager::getInstance()->assertAvailable(
            resourceKey: CatalogManager::RESOURCE_KEY,
            recordId: $catalogId,
            claimToken: $claimToken !== '' ? $claimToken : null
        );
    }

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
