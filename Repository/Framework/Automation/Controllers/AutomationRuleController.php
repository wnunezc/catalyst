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

namespace Catalyst\Repository\Automation\Controllers;

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
use Catalyst\Repository\Automation\Actions\AutomationRuleExecutionService;
use Catalyst\Repository\Automation\Actions\AutomationRuleMutationService;
use Catalyst\Repository\Automation\Requests\AutomationRuleRequest;
use Catalyst\Repository\Automation\Requests\AutomationRuleTransitionRequest;
use Catalyst\Repository\Automation\Requests\AutomationRunContextRequest;
use Catalyst\Repository\Automation\Support\AutomationManualRunState;
use Catalyst\Repository\Automation\Support\AutomationRuleFormFactory;
use Catalyst\Repository\Automation\Support\AutomationRuleGridFactory;
use Catalyst\Repository\Automation\Support\AutomationRuleShowDataFactory;
use RuntimeException;

/**
 * Defines the Automation Rule Controller class contract.
 *
 * @package Catalyst\Repository\Automation\Controllers
 * Responsibility: Coordinates the automation rule controller behavior within its module boundary.
 */
final class AutomationRuleController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    /**
     * Initializes the Automation Rule Controller instance.
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
     * Handles the index workflow.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', AutomationManager::RESOURCE_KEY);

        return $this->view('automation.index', [
            'title' => __('automation.index.title'),
            'pageTitle' => __('automation.index.title'),
            'grid' => $this->gridFactory->build($this->repository)->resolve($request),
        ], 200, 'admin');
    }

    /**
     * Handles the create workflow.
     */
    public function create(Request $request): Response
    {
        $this->authorizeResource('create', AutomationManager::RESOURCE_KEY);

        return $this->renderForm(__('automation.form_page.create_title'), null);
    }

    /**
     * Handles the persistence workflow.
     */
    public function store(AutomationRuleRequest $request): Response
    {
        $this->authorizeResource('create', AutomationManager::RESOURCE_KEY);
        $rule = $this->manager->create($request->validated());

        return $this->postActionSuccessRedirect('/automation-rules/' . (int) $rule->getKey(), __('automation.messages.created'));
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

    /**
     * Handles the update workflow.
     */
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

    /**
     * Handles the destroy workflow.
     */
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

    /**
     * Executes the command workflow.
     */
    public function run(AutomationRunContextRequest $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/automation-rules', __('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('run', AutomationManager::RESOURCE_KEY, $rule->toArray());
        $result = null;

        try {
            $execution = $this->executionService->execute($request->request(), $rule, $request->context(), 'manual', true);
            $result = is_array($execution['outcome']['result'] ?? null) ? $execution['outcome']['result'] : null;
            $this->manualRunState->stash((int) $rule->getKey(), $result, $request->contextJson());

            if (($execution['outcome']['ok'] ?? false) !== true) {
                return $this->postActionErrorRedirect(
                    '/automation-rules/' . (int) $rule->getKey(),
                    (string) ($execution['outcome']['message'] ?? __('automation.messages.execution_failed')),
                    (int) ($execution['outcome']['status'] ?? 422)
                );
            }

            return $this->postActionSuccessRedirect(
                '/automation-rules/' . (int) $rule->getKey(),
                $execution['replayed'] ? __('automation.messages.execution_reused') : __('automation.messages.executed')
            );
        } catch (IdempotencyConflictException|IdempotencyInProgressException $e) {
            $this->manualRunState->stash((int) $rule->getKey(), $result, $request->contextJson());

            return $this->postActionErrorRedirect('/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 409);
        } catch (RuntimeException $e) {
            $this->manualRunState->stash((int) $rule->getKey(), $result, $request->contextJson());

            return $this->postActionErrorRedirect('/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 422);
        }
    }

    /**
     * Handles the transition workflow.
     */
    public function transition(Request $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->postActionErrorRedirect('/automation-rules', __('automation.messages.not_found'), 404);
        }

        $payload = new AutomationRuleTransitionRequest($request);
        if (!$payload->hasTransition()) {
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $rule->getKey(), __('automation.messages.select_transition'));
        }

        try {
            $this->mutationService->transition($rule, $request, $payload->transition(), $payload->notes());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/automation-rules/' . (int) $rule->getKey(), $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/automation-rules/' . (int) $rule->getKey(), __('automation.messages.transitioned'));
    }

    /**
     * Handles the restore version workflow.
     */
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

    /**
     * Renders the current view state.
     */
    private function renderShow(int $id): Response
    {
        $rule = $this->repository->find($id);
        if ($rule === null) {
            $this->flash()->error(__('automation.messages.not_found'));

            return $this->redirect('/automation-rules');
        }

        $this->authorizeResource('view', AutomationManager::RESOURCE_KEY, $rule);

        try {
            $claim = $this->acquireRecordClaim(AutomationManager::RESOURCE_KEY, $id, ['surface' => 'automation.show']);
        } catch (RuntimeException) {
            $claim = RecordClaimManager::getInstance()->snapshot(AutomationManager::RESOURCE_KEY, $id);
        }

        return $this->view(
            'automation.show',
            $this->showDataFactory->build($rule, $id, $this->buildRecordClaimContext($claim)),
            200,
            'admin'
        );
    }

    /**
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
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }
}
