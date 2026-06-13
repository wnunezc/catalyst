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

namespace Catalyst\Repository\Api\Controllers;

use Catalyst\Entities\AutomationRule;
use Catalyst\Entities\ContentVersion;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Framework\Versioning\VersionRepository;
use RuntimeException;

/**
 * API controller for version history lookup and restore operations.
 *
 * @package Catalyst\Repository\Api\Controllers
 * Responsibility: Resolves versioned resources, authorizes access, returns history,
 * and restores stored content versions through the versioning manager.
 */
final class VersionApiController extends Controller
{
    /**
     * Receives version storage and restore services for API version endpoints.
     *
     * Responsibility: Receives version storage and restore services for API version endpoints.
     */
    public function __construct(
        private readonly VersionRepository $repository,
        private readonly VersionManager $manager
    ) {
        parent::__construct();
    }

    /**
     * Lists the version history for a supported resource record after resource authorization.
     *
     * Responsibility: Lists the version history for a supported resource record after resource authorization.
     */
    public function index(Request $request, string $resourceKey, string $recordId): Response
    {
        $resourceKey = trim($resourceKey);
        $recordId = (int) $recordId;
        $record = $this->resolveRecord($resourceKey, $recordId);

        if ($record === null) {
            return $this->jsonError(__('api.messages.versioned_resource_not_found'), 404);
        }

        $this->authorizeResource('view', $this->authorizationResource($resourceKey), $record->toArray());

        return $this->jsonSuccess(
            $this->repository->listFor($resourceKey, $recordId),
            __('api.messages.version_history_retrieved')
        );
    }

    /**
     * Restores a stored content version and captures a follow-up version entry for the restored record.
     *
     * Responsibility: Restores a stored content version and captures a follow-up version entry for the restored record.
     */
    public function restore(Request $request, string $id): Response
    {
        $version = $this->repository->findModel((int) $id);
        if (!$version instanceof ContentVersion) {
            return $this->jsonError(__('api.messages.version_not_found'), 404);
        }

        $payload = $version->toArray();
        $resourceKey = (string) ($payload['resource_key'] ?? '');
        $recordId = (int) ($payload['record_id'] ?? 0);
        $record = $this->resolveRecord($resourceKey, $recordId);

        if ($record === null) {
            return $this->jsonError(__('api.messages.versioned_resource_not_found'), 404);
        }

        $this->authorizeResource('restore', $this->authorizationResource($resourceKey), $record->toArray());

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

        return $this->jsonSuccess($restored, __('api.messages.version_restored'));
    }

    /**
     * Maps an API resource key and record id to its versioned entity instance.
     *
     * Responsibility: Maps an API resource key and record id to its versioned entity instance.
     */
    private function resolveRecord(string $resourceKey, int $recordId): DocumentTemplate|AutomationRule|null
    {
        return match ($resourceKey) {
            'document-templates' => DocumentTemplate::find($recordId),
            'automation-rules' => AutomationRule::find($recordId),
            default => null,
        };
    }

    private function authorizationResource(string $resourceKey): string
    {
        return match ($resourceKey) {
            'document-templates' => 'workspaces-document-templates',
            'automation-rules' => 'operations-automation-rules',
            default => $resourceKey,
        };
    }
}
