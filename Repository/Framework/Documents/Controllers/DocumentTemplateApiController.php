<?php

declare(strict_types=1);

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

final class DocumentTemplateApiController extends Controller
{
    public function __construct(
        private readonly DocumentTemplateRepository $repository,
        private readonly WorkflowManager $workflows,
        private readonly VersionRepository $versions,
        private readonly DocumentTemplatePreviewService $previewService,
        private readonly DocumentTemplateExportService $exportService
    ) {
        parent::__construct();
    }

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
