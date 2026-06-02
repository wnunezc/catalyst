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

/**
 * Defines the Version Api Controller class contract.
 *
 * @package Catalyst\Repository\ApiPlatform\Controllers
 * Responsibility: Coordinates the version api controller behavior within its module boundary.
 */
final class VersionApiController extends Controller
{
    /**
     * Initializes the Version Api Controller instance.
     */
    public function __construct(
        private readonly VersionRepository $repository,
        private readonly VersionManager $manager
    ) {
        parent::__construct();
    }

    /**
     * Handles the index workflow.
     */
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

    /**
     * Handles the restore workflow.
     */
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

    /**
     * Resolves the requested value.
     */
    private function resolveRecord(string $resourceKey, int $recordId): DocumentTemplate|AutomationRule|null
    {
        return match ($resourceKey) {
            'document-templates' => DocumentTemplate::find($recordId),
            'automation-rules' => AutomationRule::find($recordId),
            default => null,
        };
    }
}
