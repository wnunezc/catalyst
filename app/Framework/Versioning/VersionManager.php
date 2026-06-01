<?php

declare(strict_types=1);

namespace Catalyst\Framework\Versioning;

use Catalyst\Entities\AutomationRule;
use Catalyst\Entities\CatalogDefinition;
use Catalyst\Entities\ContentVersion;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;

final class VersionManager
{
    use SingletonTrait;

    private VersionRepository $repository;

    protected function __construct()
    {
        $this->repository = VersionRepository::getInstance();
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    public function capture(string $resourceKey, int $recordId, array $snapshot, ?string $summary = null): ContentVersion
    {
        $latest = $this->repository->latest($resourceKey, $recordId);
        $latestSnapshot = is_array($latest['snapshot_json'] ?? null) ? (array) $latest['snapshot_json'] : [];
        $diff = $this->diff($latestSnapshot, $snapshot);

        return ContentVersion::create([
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'version_number' => $this->repository->nextVersionNumber($resourceKey, $recordId),
            'summary' => $summary,
            'snapshot_json' => $snapshot,
            'diff_json' => $diff,
            'actor_id' => AuthManager::getInstance()->id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function restore(int $versionId): array
    {
        $version = $this->repository->findModel($versionId);
        if (!$version instanceof ContentVersion) {
            throw new RuntimeException('Version not found.');
        }

        $payload = $version->toArray();
        $resourceKey = (string) ($payload['resource_key'] ?? '');
        $recordId = (int) ($payload['record_id'] ?? 0);
        $snapshot = (array) ($payload['snapshot_json'] ?? []);

        match ($resourceKey) {
            'document-templates' => $this->restoreDocumentTemplate($recordId, $snapshot),
            'automation-rules' => $this->restoreAutomationRule($recordId, $snapshot),
            CatalogManager::RESOURCE_KEY => $this->restoreCatalog($recordId, $snapshot),
            default => throw new RuntimeException(sprintf('Resource "%s" is not restorable.', $resourceKey)),
        };

        $restoredModel = match ($resourceKey) {
            'document-templates' => DocumentTemplate::find($recordId),
            'automation-rules' => AutomationRule::find($recordId),
            CatalogManager::RESOURCE_KEY => CatalogDefinition::find($recordId),
            default => null,
        };

        if ($restoredModel === null) {
            throw new RuntimeException('Restored record could not be reloaded.');
        }

        return $restoredModel->toArray();
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @return array<string, array{before:mixed, after:mixed}>
     */
    private function diff(array $before, array $after): array
    {
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $diff = [];

        foreach ($keys as $key) {
            if (($before[$key] ?? null) === ($after[$key] ?? null)) {
                continue;
            }

            $diff[(string) $key] = [
                'before' => $before[$key] ?? null,
                'after' => $after[$key] ?? null,
            ];
        }

        ksort($diff);

        return $diff;
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function restoreDocumentTemplate(int $recordId, array $snapshot): void
    {
        $template = DocumentTemplate::find($recordId);
        if (!$template instanceof DocumentTemplate) {
            throw new RuntimeException('Document template not found.');
        }

        unset($snapshot['id'], $snapshot['created_at'], $snapshot['created_by']);
        $template->fill($snapshot);
        $template->save();
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function restoreAutomationRule(int $recordId, array $snapshot): void
    {
        $rule = AutomationRule::find($recordId);
        if (!$rule instanceof AutomationRule) {
            throw new RuntimeException('Automation rule not found.');
        }

        unset($snapshot['id'], $snapshot['created_at'], $snapshot['created_by']);
        $rule->fill($snapshot);
        $rule->save();
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function restoreCatalog(int $recordId, array $snapshot): void
    {
        CatalogManager::getInstance()->restoreCatalogSnapshot($recordId, $snapshot);
    }
}
