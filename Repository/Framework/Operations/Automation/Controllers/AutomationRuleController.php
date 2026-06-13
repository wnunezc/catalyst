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

namespace Catalyst\Repository\Operations\Automation\Controllers;

use Catalyst\Entities\AutomationRule;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Automation\AutomationRuleRepository;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Idempotency\IdempotencyConflictException;
use Catalyst\Framework\Idempotency\IdempotencyInProgressException;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Catalyst\Repository\Operations\Automation\Actions\AutomationRuleExecutionService;
use Catalyst\Repository\Operations\Automation\Actions\AutomationRuleMutationService;
use Catalyst\Repository\Operations\Automation\Requests\AutomationRuleRequest;
use Catalyst\Repository\Operations\Automation\Requests\AutomationRuleTransitionRequest;
use Catalyst\Repository\Operations\Automation\Requests\AutomationRunContextRequest;
use Catalyst\Repository\Operations\Automation\Support\AutomationManualRunState;
use Catalyst\Repository\Operations\Automation\Support\AutomationRuleFormFactory;
use Catalyst\Repository\Operations\Automation\Support\AutomationRuleGridFactory;
use Catalyst\Repository\Operations\Automation\Support\AutomationRuleShowDataFactory;
use RuntimeException;

/**
 * Serves the administrative automation rule workflow.
 *
 * @package Catalyst\Repository\Operations\Automation\Controllers
 * Responsibility: Render automation rule screens and coordinate authorized CRUD, execution and workflow actions.
 */
final class AutomationRuleController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    /**
     * Initializes the Automation Rule Controller instance.
     *
     * Responsibility: Initializes the Automation Rule Controller instance.
     */
    public function __construct(
        private readonly AutomationRuleRepository $repository,
        private readonly AutomationManager $manager,
        private readonly AutomationRuleMutationService $mutationService,
        private readonly AutomationRuleExecutionService $executionService,
        private readonly AutomationManualRunState $manualRunState,
        private readonly AutomationRuleGridFactory $gridFactory,
        private readonly AutomationRuleFormFactory $formFactory,
        private readonly AutomationRuleShowDataFactory $showDataFactory
    ) {
        parent::__construct();
    }

    /**
     * Renders the searchable automation rule listing.
     *
     * Responsibility: Renders the searchable automation rule listing.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'operations-automation-rules');

        return $this->view('automation.index', [
            'title' => __('automation.index.title'),
            'pageTitle' => __('automation.index.title'),
            'grid' => $this->gridFactory->build($this->repository)->resolve($request),
        ]);
    }

    /**
     * Renders the automation rule creation form.
     *
     * Responsibility: Renders the automation rule creation form.
     */
    public function create(Request $request): Response
    {
        $this->authorizeResource('create', 'operations-automation-rules');

        return $this->renderForm(__('automation.form_page.create_title'), null);
    }

    /**
     * Persists a validated automation rule and redirects to its detail view.
     *
     * Responsibility: Persists a validated automation rule and redirects to its detail view.
     */
    public function store(AutomationRuleRequest $request): Response
    {
        $this->authorizeResource('create', 'operations-automation-rules');
        $rule = $this->manager->create($request->validated());

        return $this->postActionSuccessRedirect('/operations/automation-rules/' . (int) $rule->getKey(), __('automation.messages.created'));
    }

    /**
     * Renders the selected automation rule detail view.
     *
     * Responsibility: Renders the selected automation rule detail view.
     */
    public function show(Request $request, string $id): Response
    {
        return $this->renderShow((int) $id);
    }

    /**
     * Acquires a record claim and renders the automation rule edit form.
     *
     * Responsibility: Acquires a record claim and renders the automation rule edit form.
     */
    public function edit(Request $request, string $id): Response
    {
        $rule = $this->repository->find((int) $id);
        if ($rule === null) {
            $this->flash()->error(__('automation.messages.not_found'));

            return $this->redirect('/operations/automation-rules');
        }

        $this->authorizeResource('view', 'operations-automation-rules', $rule);

        try {
            $claim = $this->acquireRecordClaim(AutomationManager::RESOURCE_KEY, (int) $id, [
                'surface' => 'automation.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/operations/automation-rules/' . (int) $id);
        }

        return $this->renderForm(__('automation.form_page.edit_title'), $rule, $claim);
    }

    /**
     * Updates an automation rule while handling concurrency conflicts.
     *
     * Responsibility: Updates an automation rule while handling concurrency conflicts.
     */
    public function update(AutomationRuleRequest $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/operations/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('update', 'operations-automation-rules', $rule->toArray());

        try {
            $this->mutationService->update($rule, $request->request(), $request->validated());
            $this->toast('success', __('automation.messages.updated'));
        } catch (OptimisticLockException|RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);

            return $this->postActionErrorRedirect('/operations/automation-rules/' . (int) $id . '/edit', $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/operations/automation-rules/' . (int) $rule->getKey(), __('automation.messages.updated'));
    }

    /**
     * Deletes an automation rule through the claim-protected mutation service.
     *
     * Responsibility: Deletes an automation rule through the claim-protected mutation service.
     */
    public function destroy(Request $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/operations/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('delete', 'operations-automation-rules', $rule->toArray());

        try {
            $this->mutationService->delete($rule, $request);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/operations/automation-rules/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/operations/automation-rules', __('automation.messages.deleted'));
    }

    /**
     * Executes the command workflow.
     *
     * Responsibility: Executes the command workflow.
     */
    public function run(AutomationRunContextRequest $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/operations/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('run', 'operations-automation-rules', $rule->toArray());
        $result = null;

        try {
            $execution = $this->executionService->execute($request->request(), $rule, $request->context(), 'manual', true);
            $result = is_array($execution['outcome']['result'] ?? null) ? $execution['outcome']['result'] : null;
            $this->manualRunState->stash((int) $rule->getKey(), $result, $request->contextJson());

            if (($execution['outcome']['ok'] ?? false) !== true) {
                return $this->postActionErrorRedirect(
                    '/operations/automation-rules/' . (int) $rule->getKey(),
                    (string) ($execution['outcome']['message'] ?? __('automation.messages.execution_failed')),
                    (int) ($execution['outcome']['status'] ?? 422)
                );
            }

            return $this->postActionSuccessRedirect(
                '/operations/automation-rules/' . (int) $rule->getKey(),
                $execution['replayed'] ? __('automation.messages.execution_reused') : __('automation.messages.executed')
            );
        } catch (IdempotencyConflictException|IdempotencyInProgressException $e) {
            $this->manualRunState->stash((int) $rule->getKey(), $result, $request->contextJson());

            return $this->postActionErrorRedirect('/operations/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 409);
        } catch (RuntimeException $e) {
            $this->manualRunState->stash((int) $rule->getKey(), $result, $request->contextJson());

            return $this->postActionErrorRedirect('/operations/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 422);
        }
    }

    /**
     * Applies the requested workflow transition to an automation rule.
     *
     * Responsibility: Applies the requested workflow transition to an automation rule.
     */
    public function transition(Request $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/operations/automation-rules', __('automation.messages.not_found'), 404);
        }

        $payload = new AutomationRuleTransitionRequest($request);
        if (!$payload->hasTransition()) {
            return $this->postActionErrorRedirect('/operations/automation-rules/' . (int) $rule->getKey(), __('automation.messages.select_transition'));
        }

        try {
            $this->mutationService->transition($rule, $request, $payload->transition(), $payload->notes());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/operations/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/operations/automation-rules/' . (int) $rule->getKey(), __('automation.messages.transitioned'));
    }

    /**
     * Restores a selected captured version of an automation rule.
     *
     * Responsibility: Restores a selected captured version of an automation rule.
     */
    public function restoreVersion(Request $request, string $id, string $versionId): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/operations/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('restore', 'operations-automation-rules', $rule->toArray());

        try {
            $this->mutationService->restoreVersion($rule, $request, (int) $versionId);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/operations/automation-rules/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/operations/automation-rules/' . (int) $id, __('automation.messages.version_restored'));
    }

    /**
     * Builds and renders the automation rule detail state, including its record claim snapshot.
     *
     * Responsibility: Builds and renders the automation rule detail state, including its record claim snapshot.
     */
    private function renderShow(int $id): Response
    {
        $rule = $this->repository->find($id);
        if ($rule === null) {
            $this->flash()->error(__('automation.messages.not_found'));

            return $this->redirect('/operations/automation-rules');
        }

        $this->authorizeResource('view', 'operations-automation-rules', $rule);

        try {
            $claim = $this->acquireRecordClaim(AutomationManager::RESOURCE_KEY, $id, ['surface' => 'automation.show']);
        } catch (RuntimeException) {
            $claim = RecordClaimManager::getInstance()->snapshot(AutomationManager::RESOURCE_KEY, $id);
        }

        return $this->view(
            'automation.show',
            $this->showDataFactory->build($rule, $id, $this->buildRecordPresenceContext($claim)),
            200
        );
    }

    /**
     * Builds and renders the create or edit form for an automation rule.
     *
     * Responsibility: Builds and renders the create or edit form for an automation rule.
     * @param array<string, mixed>|null $rule
     * @param array<string, mixed>|null $claim
     */
    private function renderForm(string $title, ?array $rule, ?array $claim = null): Response
    {
        return $this->view('automation.form', [
            'title' => $title,
            'pageTitle' => $title,
            'rule' => $rule,
            'form' => $this->formFactory->build(
                $rule,
                $this->concurrencyHiddenFields($claim, $rule !== null ? (int) ($rule['lock_version'] ?? 1) : null)
            ),
            'recordPresence' => $this->buildRecordPresenceContext($claim),
        ]);
    }
}
