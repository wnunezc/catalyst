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
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Idempotency\IdempotencyConflictException;
use Catalyst\Framework\Idempotency\IdempotencyInProgressException;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Repository\Automation\Actions\AutomationRuleExecutionService;
use Catalyst\Repository\Automation\Requests\AutomationRuleIndexRequest;
use Catalyst\Repository\Automation\Requests\AutomationRunContextRequest;
use RuntimeException;

/**
 * Defines the Automation Rule Api Controller class contract.
 *
 * @package Catalyst\Repository\Automation\Controllers
 * Responsibility: Coordinates the automation rule api controller behavior within its module boundary.
 */
final class AutomationRuleApiController extends Controller
{
    /**
     * Initializes the Automation Rule Api Controller instance.
     */
    public function __construct(
        private readonly AutomationRuleRepository $repository,
        private readonly WorkflowManager $workflows,
        private readonly VersionRepository $versions,
        private readonly AutomationRuleExecutionService $executionService
    ) {
        parent::__construct();
    }

    /**
     * Handles the api index workflow.
     */
    public function apiIndex(AutomationRuleIndexRequest $request): Response
    {
        $this->authorizeResource('view-any', AutomationManager::RESOURCE_KEY);
        $criteria = $request->criteria();
        $result = $this->repository->search($criteria);

        return $this->resourceJsonSuccess(AutomationManager::RESOURCE_KEY, $result['rows'] ?? [], __('automation.messages.retrieved'), 200, [
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

    /**
     * Handles the api run workflow.
     */
    public function apiRun(AutomationRunContextRequest $request, string $id): Response
    {
        $rule = $this->repository->findModel((int) $id);
        if (!$rule instanceof AutomationRule) {
            return $this->jsonError(__('automation.messages.not_found'), 404);
        }

        $this->authorizeResource('run', AutomationManager::RESOURCE_KEY, $rule->toArray());

        try {
            $execution = $this->executionService->execute($request->request(), $rule, $request->context(), 'api', false);
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
}
