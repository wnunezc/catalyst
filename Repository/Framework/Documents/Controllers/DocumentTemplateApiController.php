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

namespace Catalyst\Repository\Documents\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Document\DocumentTemplateRepository;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Repository\Documents\Actions\DocumentTemplateExportService;
use Catalyst\Repository\Documents\Actions\DocumentTemplatePreviewService;
use Catalyst\Repository\Documents\Requests\DocumentExportPayloadRequest;
use Catalyst\Repository\Documents\Requests\DocumentPreviewPayloadRequest;
use Catalyst\Repository\Documents\Requests\DocumentTemplateIndexRequest;
use RuntimeException;

/**
 * Defines the Document Template Api Controller class contract.
 *
 * @package Catalyst\Repository\Documents\Controllers
 * Responsibility: Coordinates the document template api controller behavior within its module boundary.
 */
final class DocumentTemplateApiController extends Controller
{
    /**
     * Initializes the Document Template Api Controller instance.
     */
    public function __construct(
        private readonly DocumentTemplateRepository $repository,
        private readonly WorkflowManager $workflows,
        private readonly VersionRepository $versions,
        private readonly DocumentTemplatePreviewService $previewService,
        private readonly DocumentTemplateExportService $exportService
    ) {
        parent::__construct();
    }

    /**
     * Handles the api index workflow.
     */
    public function apiIndex(DocumentTemplateIndexRequest $request): Response
    {
        $this->authorizeResource('view-any', DocumentTemplateManager::RESOURCE_KEY);
        $criteria = $request->criteria();
        $result = $this->repository->search($criteria);

        return $this->resourceJsonSuccess(DocumentTemplateManager::RESOURCE_KEY, $result['rows'] ?? [], __('documents.messages.templates_retrieved'), 200, [
            'page' => $criteria['page'],
            'per_page' => $criteria['per_page'],
            'total' => (int) ($result['total'] ?? 0),
        ]);
    }

    /**
     * Handles the api show workflow.
     */
    public function apiShow(Request $request, string $id): Response
    {
        $template = $this->repository->find((int) $id);
        if ($template === null) {
            return $this->jsonError(__('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('view', DocumentTemplateManager::RESOURCE_KEY, $template);

        return $this->jsonSuccess([
            'template' => $this->sanitizeResourcePayload(DocumentTemplateManager::RESOURCE_KEY, $template),
            'artifacts' => $this->sanitizeResourcePayload('document-artifacts', $this->repository->artifactsForTemplate((int) $id)),
            'versions' => $this->sanitizeVersionPayloads(DocumentTemplateManager::RESOURCE_KEY, $this->versions->listFor(DocumentTemplateManager::RESOURCE_KEY, (int) $id)),
            'available_transitions' => $this->workflows->availableTransitionsForResource(
                DocumentTemplateManager::WORKFLOW_KEY,
                DocumentTemplateManager::RESOURCE_KEY,
                (int) $id,
                $template
            ),
        ], __('documents.messages.template_retrieved'));
    }

    /**
     * Handles the api preview workflow.
     */
    public function apiPreview(DocumentPreviewPayloadRequest $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->jsonError(__('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('view', DocumentTemplateManager::RESOURCE_KEY, $template->toArray());

        try {
            $preview = $this->previewService->preview($template, $request->payload());
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        return $this->jsonSuccess($preview, __('documents.messages.preview_generated'));
    }

    /**
     * Handles the api export workflow.
     */
    public function apiExport(DocumentExportPayloadRequest $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->jsonError(__('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('export', DocumentTemplateManager::RESOURCE_KEY, $template->toArray());

        try {
            $artifact = $this->exportService->export($template, $request->payload());
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        return $this->jsonSuccess($artifact->toArray(), __('documents.messages.artifact_exported'), 201);
    }
}
