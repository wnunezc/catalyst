<?php

declare(strict_types=1);

namespace Catalyst\Repository\Automation\Controllers;

use Catalyst\Entities\AutomationRule;
use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Automation\AutomationRuleRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Idempotency\IdempotencyConflictException;
use Catalyst\Framework\Idempotency\IdempotencyInProgressException;
use Catalyst\Framework\Idempotency\IdempotencyManager;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Temporal\EffectiveWindow;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Catalyst\Repository\Automation\Actions\AutomationRuleMutationService;
use Catalyst\Repository\Automation\Requests\AutomationRunContextRequest;
use Catalyst\Repository\Automation\Requests\AutomationRuleRequest;
use RuntimeException;

final class AutomationRuleController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    public function __construct(
        private readonly AutomationRuleRepository $repository,
        private readonly AutomationManager $manager,
        private readonly WorkflowManager $workflows,
        private readonly WorkflowRepository $workflowRepository,
        private readonly VersionRepository $versions,
        private readonly AutomationRuleMutationService $mutationService
    ) {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', AutomationManager::RESOURCE_KEY);

        $gridBuilder = DataGrid::make()
            ->baseUrl('/automation-rules')
            ->title(__('automation.index.title'), __('automation.index.description'))
            ->emptyState(
                __('automation.index.empty.title'),
                __('automation.index.empty.description'),
                [
                    'label' => __('automation.index.empty.action'),
                    'href' => '/automation-rules/create',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-plus',
                ]
            )
            ->columns([
                [
                    'key' => 'name',
                    'label' => __('automation.index.columns.rule'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['name'] ?? ''),
                        (string) ($row['slug'] ?? '')
                    ),
                ],
                [
                    'key' => 'trigger_type',
                    'label' => __('automation.index.columns.trigger'),
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['trigger_type'] ?? 'event'),
                        (string) (($row['event_name'] ?? '') ?: ($row['cron_expression'] ?? ''))
                    ),
                ],
                [
                    'key' => 'action_type',
                    'label' => __('automation.index.columns.action'),
                ],
                [
                    'key' => 'current_state',
                    'label' => __('automation.index.columns.workflow'),
                    'value' => static fn (array $row): array => DataGrid::badge((string) ($row['current_state'] ?? 'draft')),
                ],
                [
                    'key' => 'temporal_state',
                    'label' => __('automation.index.columns.validity'),
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['temporal_state'] ?? 'active'),
                        (string) (($row['valid_from'] ?? null) ?: __('automation.show.common.now'))
                            . ' → '
                            . (string) (($row['valid_to'] ?? null) ?: __('automation.show.common.open'))
                    ),
                ],
                [
                    'key' => 'last_run_at',
                    'label' => __('automation.index.columns.last_run'),
                    'sortable' => true,
                ],
                [
                    'key' => 'updated_at',
                    'label' => __('automation.index.columns.updated'),
                    'sortable' => true,
                ],
            ])
            ->filters([
                [
                    'name' => 'trigger_type',
                    'label' => __('automation.index.filters.trigger'),
                    'type' => 'select',
                    'options' => [
                        'event' => __('automation.index.triggers.event'),
                        'schedule' => __('automation.index.triggers.schedule'),
                    ],
                ],
                [
                    'name' => 'state',
                    'label' => __('automation.index.filters.workflow_state'),
                    'type' => 'select',
                    'options' => [
                        'draft' => __('automation.index.states.draft'),
                        'active' => __('automation.index.states.active'),
                        'paused' => __('automation.index.states.paused'),
                        'archived' => __('automation.index.states.archived'),
                    ],
                ],
                [
                    'name' => 'temporal_state',
                    'label' => __('automation.index.filters.validity'),
                    'type' => 'select',
                    'options' => [
                        EffectiveWindow::STATE_ACTIVE => __('automation.index.validity.active'),
                        EffectiveWindow::STATE_SCHEDULED => __('automation.index.validity.scheduled'),
                        EffectiveWindow::STATE_EXPIRED => __('automation.index.validity.expired'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => __('automation.index.actions.view'),
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'href' => '/automation-rules/{id}',
                ],
                [
                    'label' => __('automation.index.actions.edit'),
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/automation-rules/{id}/edit',
                ],
                [
                    'label' => __('automation.index.actions.run'),
                    'class' => 'btn btn-outline-success btn-sm',
                    'method' => 'POST',
                    'href' => static fn (array $row): string => '/automation-rules/' . (int) ($row['id'] ?? 0)
                        . '/run?_idempotency_key=' . rawurlencode(IdempotencyManager::getInstance()->generateKey()),
                ],
                [
                    'label' => __('automation.index.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/automation-rules/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        __('automation.index.actions.confirm_delete'),
                        (string) ($row['name'] ?? __('automation.show.rule_fallback'))
                    ),
                ],
            ])
            ->resourceKey(AutomationManager::RESOURCE_KEY)
            ->defaultSort('updated_at', 'desc')
            ->pagination(15, [15, 30, 60])
            ->searchPlaceholder(__('automation.index.search_placeholder'))
            ->provider(fn (array $state): array => $this->repository->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'search' => $state['search'],
                'trigger_type' => $state['filters']['trigger_type'] ?? '',
                'state' => $state['filters']['state'] ?? '',
                'temporal_state' => $state['filters']['temporal_state'] ?? '',
            ]));

        return $this->view('automation.index', [
            'title' => __('automation.index.title'),
            'pageTitle' => __('automation.index.title'),
            'grid' => $gridBuilder->resolve($request),
        ], 200, 'admin');
    }

    public function create(Request $request): Response
    {
        $this->authorizeResource('create', AutomationManager::RESOURCE_KEY);

        return $this->renderForm(__('automation.form_page.create_title'), null);
    }

    public function store(AutomationRuleRequest $request): Response
    {
        $this->authorizeResource('create', AutomationManager::RESOURCE_KEY);
        $rule = $this->manager->create($request->validated());
        return $this->postActionSuccessRedirect('/automation-rules/' . (int) $rule->getKey(), __('automation.messages.created'));
    }

    public function show(Request $request, string $id): Response
    {
        return $this->renderShow((int) $id, null, null);
    }

    public function edit(Request $request, string $id): Response
    {
        $rule = $this->repository->find((int) $id);
        if ($rule === null) {
            $this->flash()->error(__('automation.messages.not_found'));

            return $this->redirect('/automation-rules');
        }

        $this->authorizeResource('view', AutomationManager::RESOURCE_KEY, $rule);

        try {
            $claim = $this->acquireRecordClaim(AutomationManager::RESOURCE_KEY, (int) $id, [
                'surface' => 'automation.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/automation-rules/' . (int) $id);
        }

        return $this->renderForm(__('automation.form_page.edit_title'), $rule, $claim);
    }

    public function update(AutomationRuleRequest $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('update', AutomationManager::RESOURCE_KEY, $rule->toArray());

        try {
            $this->mutationService->update($rule, $request->request(), $request->validated());
            $this->toast('success', __('automation.messages.updated'));
        } catch (OptimisticLockException|RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $id . '/edit', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/automation-rules/' . (int) $rule->getKey(), __('automation.messages.updated'));
    }

    public function destroy(Request $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('delete', AutomationManager::RESOURCE_KEY, $rule->toArray());

        try {
            $this->mutationService->delete($rule, $request);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $id, $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/automation-rules', __('automation.messages.deleted'));
    }

    public function run(AutomationRunContextRequest $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('run', AutomationManager::RESOURCE_KEY, $rule->toArray());
        $result = null;

        try {
            $this->assertRecordClaimAvailable(AutomationManager::RESOURCE_KEY, (int) $id, $request->request());
            $execution = $this->runIdempotentExecution($request->request(), $rule, $request->context(), 'manual');

            if (($execution['outcome']['ok'] ?? false) !== true) {
                $result = is_array($execution['outcome']['result'] ?? null)
                    ? $execution['outcome']['result']
                    : null;
                $this->stashManualRunState(
                    (int) $rule->getKey(),
                    $result,
                    $request->contextJson()
                );

                return $this->postActionErrorRedirect(
                    '/automation-rules/' . (int) $rule->getKey(),
                    (string) ($execution['outcome']['message'] ?? __('automation.messages.execution_failed')),
                    (int) ($execution['outcome']['status'] ?? 422)
                );
            }

            $this->stashManualRunState(
                (int) $rule->getKey(),
                is_array($execution['outcome']['result'] ?? null) ? $execution['outcome']['result'] : null,
                $request->contextJson()
            );

            return $this->postActionSuccessRedirect(
                '/automation-rules/' . (int) $rule->getKey(),
                $execution['replayed'] ? __('automation.messages.execution_reused') : __('automation.messages.executed')
            );
        } catch (IdempotencyConflictException|IdempotencyInProgressException $e) {
            $this->stashManualRunState((int) $rule->getKey(), $result, $request->contextJson());
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 409);
        } catch (RuntimeException $e) {
            $this->stashManualRunState((int) $rule->getKey(), $result ?? null, $request->contextJson());
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 422);
        }
    }

    public function transition(Request $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/automation-rules', __('automation.messages.not_found'), 404);
        }

        $transition = trim((string) $request->input('transition', ''));
        if ($transition === '') {
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $rule->getKey(), __('automation.messages.select_transition'));
        }

        try {
            $this->mutationService->transition(
                $rule,
                $request,
                $transition,
                trim((string) $request->input('notes', '')) ?: null
            );
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/automation-rules/' . (int) $rule->getKey(), __('automation.messages.transitioned'));
    }

    public function restoreVersion(Request $request, string $id, string $versionId): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('restore', AutomationManager::RESOURCE_KEY, $rule->toArray());

        try {
            $this->mutationService->restoreVersion($rule, $request, (int) $versionId);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/automation-rules/' . (int) $id, __('automation.messages.version_restored'));
    }

    public function apiIndex(Request $request): Response
    {
        $this->authorizeResource('view-any', AutomationManager::RESOURCE_KEY);

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
        $result = $this->repository->search([
            'page' => $page,
            'per_page' => $perPage,
            'search' => trim((string) $request->input('search', '')),
            'trigger_type' => trim((string) $request->input('trigger_type', '')),
            'state' => trim((string) $request->input('state', '')),
            'temporal_state' => trim((string) $request->input('temporal_state', '')),
        ]);

        return $this->resourceJsonSuccess(AutomationManager::RESOURCE_KEY, $result['rows'] ?? [], __('automation.messages.retrieved'), 200, [
            'page' => $page,
            'per_page' => $perPage,
            'total' => (int) ($result['total'] ?? 0),
        ]);
    }

    public function apiShow(Request $request, string $id): Response
    {
        $rule = $this->repository->find((int) $id);
        if ($rule === null) {
            return $this->jsonError(__('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('view', AutomationManager::RESOURCE_KEY, $rule);

        return $this->jsonSuccess([
            'rule' => $this->sanitizeResourcePayload(AutomationManager::RESOURCE_KEY, $rule),
            'logs' => $this->sanitizeResourcePayload('automation-execution-logs', $this->repository->logsForRule((int) $id)),
            'versions' => $this->sanitizeVersionPayloads(AutomationManager::RESOURCE_KEY, $this->versions->listFor(AutomationManager::RESOURCE_KEY, (int) $id)),
            'available_transitions' => $this->workflows->availableTransitionsForResource(
                AutomationManager::WORKFLOW_KEY,
                AutomationManager::RESOURCE_KEY,
                (int) $id,
                $rule
            ),
        ], __('automation.messages.rule_retrieved'));
    }

    public function apiRun(AutomationRunContextRequest $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->jsonError(__('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('run', AutomationManager::RESOURCE_KEY, $rule->toArray());

        try {
            $execution = $this->runIdempotentExecution($request->request(), $rule, $request->context(), 'api');
        } catch (IdempotencyConflictException|IdempotencyInProgressException $e) {
            return $this->jsonError($e->getMessage(), 409);
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        $outcome = $execution['outcome'];
        if (($outcome['ok'] ?? false) !== true) {
            return $this->apiResponse(
                false,
                (string) ($outcome['message'] ?? __('automation.messages.execution_failed')),
                $outcome['result'] ?? null,
                (int) ($outcome['status'] ?? 422),
                ['idempotent_replay' => (bool) ($execution['replayed'] ?? false)]
            );
        }

        return $this->apiResponse(
            true,
            (string) ($outcome['message'] ?? __('automation.messages.executed')),
            $outcome['result'] ?? null,
            200,
            ['idempotent_replay' => (bool) ($execution['replayed'] ?? false)]
        );
    }

    /**
     * @param array<string, mixed>|null $lastRunResult
     */
    private function renderShow(int $id, ?array $lastRunResult = null, ?string $contextJson = null): Response
    {
        $rule = $this->repository->find($id);
        if ($rule === null) {
            $this->flash()->error(__('automation.messages.not_found'));

            return $this->redirect('/automation-rules');
        }

        $this->authorizeResource('view', AutomationManager::RESOURCE_KEY, $rule);
        $claim = null;

        try {
            $claim = $this->acquireRecordClaim(AutomationManager::RESOURCE_KEY, $id, [
                'surface' => 'automation.show',
            ]);
        } catch (RuntimeException) {
            $claim = \Catalyst\Framework\Concurrency\RecordClaimManager::getInstance()->snapshot(
                AutomationManager::RESOURCE_KEY,
                $id
            );
        }

        $instanceId = (int) ($rule['workflow_instance_id'] ?? 0);
        if ($lastRunResult === null && $contextJson === null) {
            $runState = $this->consumeManualRunState($id);
            if ($runState !== null) {
                $lastRunResult = $runState['result'];
                $contextJson = $runState['context_json'];
            }
        }

        $contextJson = $contextJson ?? $this->jsonField([
            'payload' => [
                'actor_id' => (int) ((\Catalyst\Framework\Auth\AuthManager::getInstance()->user()['id'] ?? 0)),
            ],
        ]);

        return $this->view('automation.show', [
            'title' => __('automation.show.title'),
            'pageTitle' => (string) ($rule['name'] ?? __('automation.show.rule_fallback')),
            'rule' => $rule,
            'versions' => $this->versions->listFor(AutomationManager::RESOURCE_KEY, $id),
            'logs' => $this->repository->logsForRule($id),
            'transitions' => $instanceId > 0 ? $this->workflowRepository->transitionsForInstance($instanceId) : [],
            'availableTransitions' => $this->workflows->availableTransitionsForResource(
                AutomationManager::WORKFLOW_KEY,
                AutomationManager::RESOURCE_KEY,
                $id,
                $rule
            ),
            'runContextJson' => $contextJson,
            'lastRunResult' => $lastRunResult,
            'runIdempotencyKey' => IdempotencyManager::getInstance()->generateKey(),
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }

    /**
     * @param array<string, mixed>|null $rule
     */
    private function renderForm(string $title, ?array $rule, ?array $claim = null): Response
    {
        $fields = array_merge(
            $this->concurrencyHiddenFields(
                $claim,
                $rule !== null ? (int) ($rule['lock_version'] ?? 1) : null
            ),
            [
                'name' => [
                    'label' => __('automation.form_page.labels.rule_name'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('automation.form_page.placeholders.rule_name'),
                    'attributes' => ['maxlength' => 150],
                ],
                'slug' => [
                    'label' => __('automation.form_page.labels.rule_slug'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('automation.form_page.placeholders.rule_slug'),
                    'attributes' => ['maxlength' => 150],
                ],
                'description' => [
                    'label' => __('automation.form_page.labels.description'),
                    'section' => 'identity',
                    'type' => 'textarea',
                    'html_attributes' => 'rows="3"',
                ],
                'trigger_type' => [
                    'label' => __('automation.form_page.labels.trigger_type'),
                    'required' => true,
                    'section' => 'identity',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'event', 'label' => __('automation.index.triggers.event')],
                        ['value' => 'schedule', 'label' => __('automation.index.triggers.schedule')],
                    ],
                    'empty_option_label' => '',
                    'value' => $rule['trigger_type'] ?? 'event',
                ],
                'event_name' => [
                    'label' => __('automation.form_page.labels.event_name'),
                    'section' => 'identity',
                    'placeholder' => __('automation.form_page.placeholders.event_name'),
                    'help' => __('automation.form_page.help.event_name'),
                ],
                'cron_expression' => [
                    'label' => __('automation.form_page.labels.cron_expression'),
                    'section' => 'identity',
                    'placeholder' => __('automation.form_page.placeholders.cron_expression'),
                    'help' => __('automation.form_page.help.cron_expression'),
                ],
                'condition_json' => [
                    'label' => __('automation.form_page.labels.condition_json'),
                    'required' => true,
                    'section' => 'conditions',
                    'type' => 'textarea',
                    'html_attributes' => 'rows="8" spellcheck="false"',
                    'help' => __('automation.form_page.help.condition_json'),
                    'value' => $this->jsonField($rule['condition_json'] ?? new \stdClass()),
                ],
                'action_type' => [
                    'label' => __('automation.form_page.labels.action_type'),
                    'required' => true,
                    'section' => 'action',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'notification', 'label' => __('automation.form_page.action_types.notification')],
                        ['value' => 'workflow_transition', 'label' => __('automation.form_page.action_types.workflow_transition')],
                        ['value' => 'render_document', 'label' => __('automation.form_page.action_types.render_document')],
                    ],
                    'empty_option_label' => '',
                    'value' => $rule['action_type'] ?? 'notification',
                ],
                'action_payload_json' => [
                    'label' => __('automation.form_page.labels.action_payload_json'),
                    'required' => true,
                    'section' => 'action',
                    'type' => 'textarea',
                    'html_attributes' => 'rows="12" spellcheck="false"',
                    'help' => __('automation.form_page.help.action_payload_json'),
                    'value' => $this->jsonField($rule['action_payload_json'] ?? [
                        'title' => __('automation.form_page.defaults.action_title'),
                        'body' => __('automation.form_page.defaults.action_body'),
                        'target_path' => 'payload.actor_id',
                    ]),
                ],
                'valid_from' => [
                    'label' => __('automation.form_page.labels.valid_from'),
                    'section' => 'identity',
                    'placeholder' => __('automation.form_page.placeholders.valid_from'),
                    'help' => __('automation.form_page.help.valid_from'),
                ],
                'valid_to' => [
                    'label' => __('automation.form_page.labels.valid_to'),
                    'section' => 'identity',
                    'placeholder' => __('automation.form_page.placeholders.valid_to'),
                    'help' => __('automation.form_page.help.valid_to'),
                ],
            ]
        );

        $form = FormBuilder::make()
            ->action($rule === null ? '/automation-rules' : '/automation-rules/' . (int) ($rule['id'] ?? 0))
            ->method('POST')
            ->model($rule)
            ->sections([
                'identity' => [
                    'title' => __('automation.form_page.sections.identity.title'),
                    'description' => __('automation.form_page.sections.identity.description'),
                ],
                'conditions' => [
                    'title' => __('automation.form_page.sections.conditions.title'),
                    'description' => __('automation.form_page.sections.conditions.description'),
                ],
                'action' => [
                    'title' => __('automation.form_page.sections.action.title'),
                    'description' => __('automation.form_page.sections.action.description'),
                ],
            ])
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $rule === null ? __('automation.form_page.actions.create') : __('automation.form_page.actions.save'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => __('automation.form_page.actions.back'),
                    'href' => '/automation-rules',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();

        return $this->view('automation.form', [
            'title' => $title,
            'pageTitle' => $title,
            'rule' => $rule,
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
     * @param array<string, mixed> $context
     * @return array{replayed:bool,outcome:array<string, mixed>}
     */
    private function runIdempotentExecution(Request $request, AutomationRule $rule, array $context, string $triggerSource): array
    {
        $idempotencyKey = $request->idempotencyKey();
        if ($idempotencyKey === '') {
            throw new RuntimeException(__('automation.messages.idempotency_required'));
        }

        return IdempotencyManager::getInstance()->execute(
            scopeKey: AutomationManager::RESOURCE_KEY . '.run.' . (int) $rule->getKey(),
            idempotencyKey: $idempotencyKey,
            fingerprint: [
                'rule_id' => (int) $rule->getKey(),
                'trigger_source' => $triggerSource,
                'context' => $context,
            ],
            callback: fn (): array => [
                'ok' => true,
                'status' => 200,
                'message' => __('automation.messages.executed'),
                'result' => $this->manager->executeRule($rule, $context, $triggerSource),
            ],
            failureMapper: static fn (\Throwable $e): array => [
                'ok' => false,
                'status' => $e instanceof RuntimeException ? 422 : 500,
                'message' => $e->getMessage(),
                'result' => null,
            ]
        );
    }

    /**
     * @param array<string, mixed>|null $result
     */
    private function stashManualRunState(int $ruleId, ?array $result, string $contextJson): void
    {
        SessionManager::getInstance()->set('_automation_manual_run_state', [
            'rule_id' => $ruleId,
            'result' => $result,
            'context_json' => $contextJson,
        ]);
    }

    /**
     * @return array{result: array<string, mixed>|null, context_json: string}|null
     */
    private function consumeManualRunState(int $ruleId): ?array
    {
        $session = SessionManager::getInstance();
        $state = $session->get('_automation_manual_run_state');

        if (!is_array($state) || (int) ($state['rule_id'] ?? 0) !== $ruleId) {
            return null;
        }

        $session->remove('_automation_manual_run_state');

        return [
            'result' => is_array($state['result'] ?? null) ? $state['result'] : null,
            'context_json' => (string) ($state['context_json'] ?? '{}'),
        ];
    }
}
