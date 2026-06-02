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

use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Document\DocumentTemplateRepository;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Catalyst\Repository\Documents\Actions\DocumentTemplateMutationService;
use Catalyst\Repository\Documents\Actions\DocumentTemplatePreviewService;
use Catalyst\Repository\Documents\Requests\DocumentExportPayloadRequest;
use Catalyst\Repository\Documents\Requests\DocumentPreviewPayloadRequest;
use Catalyst\Repository\Documents\Requests\DocumentTemplateRequest;
use Catalyst\Repository\Documents\Requests\DocumentTemplateTransitionRequest;
use Catalyst\Repository\Documents\Support\DocumentPreviewState;
use Catalyst\Repository\Documents\Support\DocumentTemplateFormFactory;
use Catalyst\Repository\Documents\Support\DocumentTemplateGridFactory;
use Catalyst\Repository\Documents\Support\DocumentTemplateShowDataFactory;
use RuntimeException;

/**
 * Defines the Document Template Controller class contract.
 *
 * @package Catalyst\Repository\Documents\Controllers
 * Responsibility: Coordinates the document template controller behavior within its module boundary.
 */
final class DocumentTemplateController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    /**
     * Initializes the Document Template Controller instance.
     */
    public function __construct(
        private readonly DocumentTemplateRepository $repository,
        private readonly DocumentTemplateManager $manager,
        private readonly DocumentTemplateMutationService $mutationService,
        private readonly DocumentTemplatePreviewService $previewService,
        private readonly DocumentPreviewState $previewState,
        private readonly DocumentTemplateGridFactory $gridFactory,
        private readonly DocumentTemplateFormFactory $formFactory,
        private readonly DocumentTemplateShowDataFactory $showDataFactory
    ) {
        parent::__construct();
    }

    /**
     * Handles the index workflow.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', DocumentTemplateManager::RESOURCE_KEY);

        return $this->view('documents.index', [
            'title' => __('documents.index.title'),
            'pageTitle' => __('documents.index.title'),
            'grid' => $this->gridFactory->build($this->repository)->resolve($request),
        ], 200, 'admin');
    }

    /**
     * Handles the create workflow.
     */
    public function create(Request $request): Response
    {
        $this->authorizeResource('create', DocumentTemplateManager::RESOURCE_KEY);

        return $this->renderForm(__('documents.form_page.create_title'), null);
    }

    /**
     * Handles the persistence workflow.
     */
    public function store(DocumentTemplateRequest $request): Response
    {
        $this->authorizeResource('create', DocumentTemplateManager::RESOURCE_KEY);
        $template = $this->manager->create($request->validated());

        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.template_created'));
    }

    /**
     * Handles the detail display workflow.
     */
    public function show(Request $request, string $id): Response
    {
        return $this->renderShow((int) $id);
    }

    /**
     * Handles the edit workflow.
     */
    public function edit(Request $request, string $id): Response
    {
        $template = $this->repository->find((int) $id);
        if ($template === null) {
            return $this->postActionErrorRedirect('/workspaces/document-templates', __('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('view', DocumentTemplateManager::RESOURCE_KEY, $template);

        try {
            $claim = $this->acquireRecordClaim(DocumentTemplateManager::RESOURCE_KEY, (int) $id, [
                'surface' => 'documents.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/workspaces/document-templates/' . (int) $id);
        }

        return $this->renderForm(__('documents.form_page.edit_title'), $template, $claim);
    }

    /**
     * Handles the update workflow.
     */
    public function update(DocumentTemplateRequest $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            $this->flash()->error(__('documents.messages.template_not_found'));

            return $this->redirect('/workspaces/document-templates');
        }

        $this->authorizeResource('update', DocumentTemplateManager::RESOURCE_KEY, $template->toArray());

        try {
            $this->mutationService->update($template, $request->request(), $request->validated());
            $this->toast('success', __('documents.messages.template_updated'));
        } catch (OptimisticLockException|RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);

            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $id . '/edit', $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.template_updated'));
    }

    /**
     * Handles the destroy workflow.
     */
    public function destroy(Request $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->postActionErrorRedirect('/workspaces/document-templates', __('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('delete', DocumentTemplateManager::RESOURCE_KEY, $template->toArray());

        try {
            $this->mutationService->delete($template, $request);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/document-templates', __('documents.messages.template_deleted'));
    }

    /**
     * Handles the preview workflow.
     */
    public function preview(DocumentPreviewPayloadRequest $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->postActionErrorRedirect('/workspaces/document-templates', __('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('view', DocumentTemplateManager::RESOURCE_KEY, $template->toArray());

        try {
            $preview = $this->previewService->preview($template, $request->payload());
            $this->previewState->stash((int) $template->getKey(), $preview, $request->payloadJson());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $template->getKey(), $e->getMessage(), 422);
        }

        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.preview_generated'), null, 0);
    }

    /**
     * Handles the export workflow.
     */
    public function export(DocumentExportPayloadRequest $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->postActionErrorRedirect('/workspaces/document-templates', __('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('export', DocumentTemplateManager::RESOURCE_KEY, $template->toArray());

        try {
            $this->mutationService->export($template, $request->request(), $request->payload());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $template->getKey(), $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.exported'));
    }

    /**
     * Handles the transition workflow.
     */
    public function transition(Request $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->postActionErrorRedirect('/workspaces/document-templates', __('documents.messages.template_not_found'), 404);
        }

        $payload = new DocumentTemplateTransitionRequest($request);
        if (!$payload->hasTransition()) {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.select_transition'));
        }

        try {
            $this->mutationService->transition($template, $request, $payload->transition(), $payload->notes());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $template->getKey(), $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.workflow_transitioned'));
    }

    /**
     * Handles the restore version workflow.
     */
    public function restoreVersion(Request $request, string $id, string $versionId): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->postActionErrorRedirect('/workspaces/document-templates', __('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('restore', DocumentTemplateManager::RESOURCE_KEY, $template->toArray());

        try {
            $this->mutationService->restoreVersion($template, $request, (int) $versionId);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $id, __('documents.messages.version_restored'));
    }

    /**
     * Renders the current view state.
     */
    private function renderShow(int $id): Response
    {
        $template = $this->repository->find($id);
        if ($template === null) {
            $this->flash()->error(__('documents.messages.template_not_found'));

            return $this->redirect('/workspaces/document-templates');
        }

        $this->authorizeResource('view', DocumentTemplateManager::RESOURCE_KEY, $template);

        try {
            $claim = $this->acquireRecordClaim(DocumentTemplateManager::RESOURCE_KEY, $id, [
                'surface' => 'documents.show',
            ]);
        } catch (RuntimeException) {
            $claim = RecordClaimManager::getInstance()->snapshot(DocumentTemplateManager::RESOURCE_KEY, $id);
        }

        return $this->view(
            'documents.show',
            $this->showDataFactory->build($template, $id, $this->buildRecordClaimContext($claim) ?? []),
            200,
            'admin'
        );
    }

    /**
     * @param array<string, mixed>|null $template
     * @param array<string, mixed>|null $claim
     */
    private function renderForm(string $title, ?array $template, ?array $claim = null): Response
    {
        return $this->view('documents.form', [
            'title' => $title,
            'pageTitle' => $title,
            'template' => $template,
            'form' => $this->formFactory->build(
                $template,
                $this->concurrencyHiddenFields($claim, $template !== null ? (int) ($template['lock_version'] ?? 1) : null)
            ),
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }
}
