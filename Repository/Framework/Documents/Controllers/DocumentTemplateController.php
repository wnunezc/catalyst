<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Controllers;

use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Document\DocumentTemplateRepository;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Catalyst\Repository\Documents\Actions\DocumentTemplateMutationService;
use Catalyst\Repository\Documents\Requests\DocumentExportPayloadRequest;
use Catalyst\Repository\Documents\Requests\DocumentPreviewPayloadRequest;
use Catalyst\Repository\Documents\Requests\DocumentTemplateRequest;
use RuntimeException;

final class DocumentTemplateController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    public function __construct(
        private readonly DocumentTemplateRepository $repository,
        private readonly DocumentTemplateManager $manager,
        private readonly WorkflowManager $workflows,
        private readonly WorkflowRepository $workflowRepository,
        private readonly VersionRepository $versions,
        private readonly DocumentTemplateMutationService $mutationService
    ) {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', DocumentTemplateManager::RESOURCE_KEY);

        $gridBuilder = DataGrid::make()
            ->baseUrl('/workspaces/document-templates')
            ->title(__('documents.index.title'), __('documents.index.description'))
            ->emptyState(
                __('documents.index.empty.title'),
                __('documents.index.empty.description'),
                [
                    'label' => __('documents.index.empty.action'),
                    'href' => '/workspaces/document-templates/create',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-plus',
                ]
            )
            ->columns([
                [
                    'key' => 'name',
                    'label' => __('documents.index.columns.template'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['name'] ?? ''),
                        (string) ($row['slug'] ?? '')
                    ),
                ],
                [
                    'key' => 'format',
                    'label' => __('documents.index.columns.format'),
                    'sortable' => true,
                ],
                [
                    'key' => 'current_state',
                    'label' => __('documents.index.columns.workflow'),
                    'sortable' => false,
                    'value' => static fn (array $row): array => DataGrid::badge((string) ($row['current_state'] ?? 'draft')),
                ],
                [
                    'key' => 'version_count',
                    'label' => __('documents.index.columns.versions'),
                ],
                [
                    'key' => 'artifact_count',
                    'label' => __('documents.index.columns.artifacts'),
                ],
                [
                    'key' => 'updated_at',
                    'label' => __('documents.index.columns.updated'),
                    'sortable' => true,
                ],
            ])
            ->filters([
                [
                    'name' => 'format',
                    'label' => __('documents.index.filters.format'),
                    'type' => 'select',
                    'options' => [
                        'html' => 'HTML',
                        'text' => __('documents.index.formats.text'),
                        'pdf' => 'PDF',
                    ],
                ],
                [
                    'name' => 'state',
                    'label' => __('documents.index.filters.workflow_state'),
                    'type' => 'select',
                    'options' => [
                        'draft' => __('documents.index.states.draft'),
                        'in_review' => __('documents.index.states.in_review'),
                        'approved' => __('documents.index.states.approved'),
                        'archived' => __('documents.index.states.archived'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => __('documents.index.actions.view'),
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'href' => '/workspaces/document-templates/{id}',
                ],
                [
                    'label' => __('documents.index.actions.edit'),
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/workspaces/document-templates/{id}/edit',
                ],
                [
                    'label' => __('documents.index.actions.export'),
                    'class' => 'btn btn-outline-success btn-sm',
                    'method' => 'POST',
                    'href' => '/workspaces/document-templates/{id}/export',
                ],
                [
                    'label' => __('documents.index.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/workspaces/document-templates/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        __('documents.index.actions.confirm_delete'),
                        (string) ($row['name'] ?? __('documents.show.template_fallback'))
                    ),
                ],
            ])
            ->defaultSort('updated_at', 'desc')
            ->pagination(15, [15, 30, 60])
            ->searchPlaceholder(__('documents.index.search_placeholder'))
            ->resourceKey(DocumentTemplateManager::RESOURCE_KEY)
            ->provider(fn (array $state): array => $this->repository->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'search' => $state['search'],
                'format' => $state['filters']['format'] ?? '',
                'state' => $state['filters']['state'] ?? '',
            ]));

        return $this->view('documents.index', [
            'title' => __('documents.index.title'),
            'pageTitle' => __('documents.index.title'),
            'grid' => $gridBuilder->resolve($request),
        ], 200, 'admin');
    }

    public function create(Request $request): Response
    {
        $this->authorizeResource('create', DocumentTemplateManager::RESOURCE_KEY);

        return $this->renderForm(__('documents.form_page.create_title'), null);
    }

    public function store(DocumentTemplateRequest $request): Response
    {
        $this->authorizeResource('create', DocumentTemplateManager::RESOURCE_KEY);
        $template = $this->manager->create($request->validated());
        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.template_created'));
    }

    public function show(Request $request, string $id): Response
    {
        return $this->renderShow((int) $id, null);
    }

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

    public function preview(DocumentPreviewPayloadRequest $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->postActionErrorRedirect('/workspaces/document-templates', __('documents.messages.template_not_found'), 404);
        }

        $this->authorizeResource('view', DocumentTemplateManager::RESOURCE_KEY, $template->toArray());

        try {
            $preview = $this->manager->preview($template, $request->payload());
            $this->stashPreviewState((int) $template->getKey(), $preview, $request->payloadJson());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $template->getKey(), $e->getMessage(), 422);
        }

        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.preview_generated'), null, 0);
    }

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

    public function transition(Request $request, string $id): Response
    {
        $template = $this->repository->findModel((int) $id);
        if ($template === null) {
            return $this->postActionErrorRedirect('/workspaces/document-templates', __('documents.messages.template_not_found'), 404);
        }

        $transition = trim((string) $request->input('transition', ''));
        if ($transition === '') {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.select_transition'));
        }

        try {
            $this->mutationService->transition(
                $template,
                $request,
                $transition,
                trim((string) $request->input('notes', '')) ?: null
            );
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/document-templates/' . (int) $template->getKey(), $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/document-templates/' . (int) $template->getKey(), __('documents.messages.workflow_transitioned'));
    }

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

    public function apiIndex(Request $request): Response
    {
        $this->authorizeResource('view-any', DocumentTemplateManager::RESOURCE_KEY);

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
        $result = $this->repository->search([
            'page' => $page,
            'per_page' => $perPage,
            'search' => trim((string) $request->input('search', '')),
            'format' => trim((string) $request->input('format', '')),
            'state' => trim((string) $request->input('state', '')),
        ]);

        return $this->resourceJsonSuccess(DocumentTemplateManager::RESOURCE_KEY, $result['rows'] ?? [], __('documents.messages.templates_retrieved'), 200, [
            'page' => $page,
            'per_page' => $perPage,
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
            $preview = $this->manager->preview($template, $request->payload());
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
            $artifact = $this->manager->export($template, $request->payload());
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        return $this->jsonSuccess($artifact->toArray(), __('documents.messages.artifact_exported'), 201);
    }

    /**
     * @param array<string, mixed>|null $preview
     */
    private function renderShow(int $id, ?array $preview = null, ?string $payloadJson = null): Response
    {
        $template = $this->repository->find($id);
        if ($template === null) {
            $this->flash()->error(__('documents.messages.template_not_found'));

            return $this->redirect('/workspaces/document-templates');
        }

        $this->authorizeResource('view', DocumentTemplateManager::RESOURCE_KEY, $template);
        $claim = null;

        try {
            $claim = $this->acquireRecordClaim(DocumentTemplateManager::RESOURCE_KEY, $id, [
                'surface' => 'documents.show',
            ]);
        } catch (RuntimeException) {
            $claim = \Catalyst\Framework\Concurrency\RecordClaimManager::getInstance()->snapshot(
                DocumentTemplateManager::RESOURCE_KEY,
                $id
            );
        }

        $instanceId = (int) ($template['workflow_instance_id'] ?? 0);
        $versions = $this->versions->listFor(DocumentTemplateManager::RESOURCE_KEY, $id);
        $artifacts = $this->repository->artifactsForTemplate($id);
        $transitions = $instanceId > 0 ? $this->workflowRepository->transitionsForInstance($instanceId) : [];
        $availableTransitions = $this->workflows->availableTransitionsForResource(
            DocumentTemplateManager::WORKFLOW_KEY,
            DocumentTemplateManager::RESOURCE_KEY,
            $id,
            $template
        );

        if ($preview === null && $payloadJson === null) {
            $previewState = $this->consumePreviewState($id);
            if ($previewState !== null) {
                $preview = $previewState['preview'];
                $payloadJson = $previewState['payload_json'];
            }
        }

        $payloadJson = $payloadJson ?? json_encode(
            (array) ($template['sample_payload_json'] ?? []),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $this->view('documents.show', [
            'title' => __('documents.module.breadcrumb_show'),
            'pageTitle' => (string) ($template['name'] ?? __('documents.show.template_fallback')),
            'template' => $template,
            'preview' => $preview,
            'previewPayloadJson' => $payloadJson ?: '{}',
            'versions' => $versions,
            'artifacts' => $artifacts,
            'transitions' => $transitions,
            'availableTransitions' => $availableTransitions,
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }

    /**
     * @param array<string, mixed>|null $template
     */
    private function renderForm(string $title, ?array $template, ?array $claim = null): Response
    {
        $fields = array_merge(
            $this->concurrencyHiddenFields(
                $claim,
                $template !== null ? (int) ($template['lock_version'] ?? 1) : null
            ),
            [
                'name' => [
                    'label' => __('documents.form_page.labels.template_name'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('documents.form_page.placeholders.template_name'),
                    'attributes' => ['maxlength' => 150],
                ],
                'slug' => [
                    'label' => __('documents.form_page.labels.template_slug'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('documents.form_page.placeholders.template_slug'),
                    'attributes' => ['maxlength' => 150],
                ],
                'format' => [
                    'label' => __('documents.index.columns.format'),
                    'required' => true,
                    'section' => 'identity',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'html', 'label' => 'HTML'],
                        ['value' => 'text', 'label' => __('documents.formats.plain_text')],
                        ['value' => 'pdf', 'label' => 'PDF'],
                    ],
                    'empty_option_label' => '',
                    'value' => $template['format'] ?? 'html',
                ],
                'description' => [
                    'label' => __('documents.form_page.labels.description'),
                    'section' => 'identity',
                    'type' => 'textarea',
                    'placeholder' => __('documents.form_page.placeholders.description'),
                    'html_attributes' => 'rows="3"',
                ],
                'variables_schema_json' => [
                    'label' => __('documents.form_page.labels.variables_schema_json'),
                    'required' => true,
                    'section' => 'variables',
                    'type' => 'textarea',
                    'help' => __('documents.form_page.help.variables_schema_json'),
                    'html_attributes' => 'rows="6" spellcheck="false"',
                    'value' => $this->jsonField($template['variables_schema_json'] ?? ['customer.name' => 'string']),
                ],
                'sample_payload_json' => [
                    'label' => __('documents.form_page.labels.sample_payload_json'),
                    'required' => true,
                    'section' => 'variables',
                    'type' => 'textarea',
                    'help' => __('documents.form_page.help.sample_payload_json'),
                    'html_attributes' => 'rows="8" spellcheck="false"',
                    'value' => $this->jsonField($template['sample_payload_json'] ?? ['customer' => ['name' => 'Catalyst']]),
                ],
                'body_template' => [
                    'label' => __('documents.form_page.labels.body_template'),
                    'required' => true,
                    'section' => 'body',
                    'type' => 'textarea',
                    'help' => __('documents.form_page.help.body_template'),
                    'html_attributes' => 'rows="16" spellcheck="false"',
                    'value' => $template['body_template'] ?? "<article>\n  <h1>{{ customer.name }}</h1>\n  {{#if invoice.total}}<p>Total: {{ invoice.total }}</p>{{/if}}\n</article>",
                ],
            ]
        );

        $form = FormBuilder::make()
            ->action($template === null ? '/workspaces/document-templates' : '/workspaces/document-templates/' . (int) ($template['id'] ?? 0))
            ->method('POST')
            ->model($template)
            ->sections([
                'identity' => [
                    'title' => __('documents.form_page.sections.identity.title'),
                    'description' => __('documents.form_page.sections.identity.description'),
                ],
                'variables' => [
                    'title' => __('documents.form_page.sections.variables.title'),
                    'description' => __('documents.form_page.sections.variables.description'),
                ],
                'body' => [
                    'title' => __('documents.form_page.sections.body.title'),
                    'description' => __('documents.form_page.sections.body.description'),
                ],
            ])
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $template === null ? __('documents.form_page.actions.create') : __('documents.form_page.actions.save'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => __('documents.form_page.actions.back'),
                    'href' => '/workspaces/document-templates',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();

        return $this->view('documents.form', [
            'title' => $title,
            'pageTitle' => $title,
            'template' => $template,
            'form' => $form,
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }

    /**
     * @param mixed $value
     */
    private function jsonField(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * @param array<string, mixed> $preview
     */
    private function stashPreviewState(int $templateId, array $preview, string $payloadJson): void
    {
        SessionManager::getInstance()->set('_document_template_preview_state', [
            'template_id' => $templateId,
            'preview' => $preview,
            'payload_json' => $payloadJson,
        ]);
    }

    /**
     * @return array{preview: array<string, mixed>|null, payload_json: string}|null
     */
    private function consumePreviewState(int $templateId): ?array
    {
        $session = SessionManager::getInstance();
        $state = $session->get('_document_template_preview_state');

        if (!is_array($state) || (int) ($state['template_id'] ?? 0) !== $templateId) {
            return null;
        }

        $session->remove('_document_template_preview_state');

        return [
            'preview' => is_array($state['preview'] ?? null) ? $state['preview'] : null,
            'payload_json' => (string) ($state['payload_json'] ?? '{}'),
        ];
    }
}
