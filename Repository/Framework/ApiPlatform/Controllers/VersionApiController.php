<?php

declare(strict_types=1);

namespace Catalyst\Repository\ApiPlatform\Controllers;

use Catalyst\Entities\AutomationRule;
use Catalyst\Entities\ContentVersion;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Framework\Versioning\VersionRepository;
use RuntimeException;

final class VersionApiController extends Controller
{
    public function __construct(
        private readonly VersionRepository $repository,
        private readonly VersionManager $manager
    ) {
        parent::__construct();
    }

    public function index(Request $request, string $resourceKey, string $recordId): Response
    {
        $resourceKey = trim($resourceKey);
        $recordId = (int) $recordId;
        $record = $this->resolveRecord($resourceKey, $recordId);

        if ($record === null) {
            return $this->jsonError(__('apiplatform.messages.versioned_resource_not_found'), 404);
        }

        $this->authorizeResource('view', $resourceKey, $record->toArray());

        return $this->jsonSuccess(
            $this->repository->listFor($resourceKey, $recordId),
            __('apiplatform.messages.version_history_retrieved')
        );
    }

    public function restore(Request $request, string $id): Response
    {
        $version = $this->repository->findModel((int) $id);
        if (!$version instanceof ContentVersion) {
            return $this->jsonError(__('apiplatform.messages.version_not_found'), 404);
        }

        $payload = $version->toArray();
        $resourceKey = (string) ($payload['resource_key'] ?? '');
        $recordId = (int) ($payload['record_id'] ?? 0);
        $record = $this->resolveRecord($resourceKey, $recordId);

        if ($record === null) {
            return $this->jsonError(__('apiplatform.messages.versioned_resource_not_found'), 404);
        }

        $this->authorizeResource('restore', $resourceKey, $record->toArray());

        try {
            $restored = $this->manager->restore((int) $id);
            $this->manager->capture(
                $resourceKey,
                $recordId,
                $restored,
                'Restaurado desde la versión API ' . (int) $id
            );
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        return $this->jsonSuccess($restored, __('apiplatform.messages.version_restored'));
    }

    private function resolveRecord(string $resourceKey, int $recordId): DocumentTemplate|AutomationRule|null
    {
        return match ($resourceKey) {
            'document-templates' => DocumentTemplate::find($recordId),
            'automation-rules' => AutomationRule::find($recordId),
            default => null,
        };
    }
}
