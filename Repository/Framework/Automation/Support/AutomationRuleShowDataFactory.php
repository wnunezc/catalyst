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

namespace Catalyst\Repository\Automation\Support;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Automation\AutomationRuleRepository;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;
use Catalyst\Repository\Automation\Actions\AutomationRuleExecutionService;

/**
 * Defines the Automation Rule Show Data Factory class contract.
 *
 * @package Catalyst\Repository\Automation\Support
 * Responsibility: Coordinates the automation rule show data factory behavior within its module boundary.
 */
final class AutomationRuleShowDataFactory
{
    /**
     * Initializes the Automation Rule Show Data Factory instance.
     */
    public function __construct(
        private readonly AutomationRuleRepository $repository,
        private readonly WorkflowManager $workflows,
        private readonly WorkflowRepository $workflowRepository,
        private readonly VersionRepository $versions,
        private readonly AutomationRuleExecutionService $executionService,
        private readonly AutomationManualRunState $manualRunState
    ) {
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, mixed> $claimContext
     * @return array<string, mixed>
     */
    public function build(array $rule, int $ruleId, array $claimContext): array
    {
        $runState = $this->manualRunState->consume($ruleId);
        $contextJson = $runState['context_json'] ?? $this->jsonField([
            'payload' => [
                'actor_id' => (int) ((AuthManager::getInstance()->user()['id'] ?? 0)),
            ],
        ]);
        $instanceId = (int) ($rule['workflow_instance_id'] ?? 0);

        return [
            'title' => __('automation.show.title'),
            'pageTitle' => (string) ($rule['name'] ?? __('automation.show.rule_fallback')),
            'rule' => $rule,
            'versions' => $this->versions->listFor(AutomationManager::RESOURCE_KEY, $ruleId),
            'logs' => $this->repository->logsForRule($ruleId),
            'transitions' => $instanceId > 0 ? $this->workflowRepository->transitionsForInstance($instanceId) : [],
            'availableTransitions' => $this->workflows->availableTransitionsForResource(
                AutomationManager::WORKFLOW_KEY,
                AutomationManager::RESOURCE_KEY,
                $ruleId,
                $rule
            ),
            'runContextJson' => $contextJson,
            'lastRunResult' => $runState['result'] ?? null,
            'runIdempotencyKey' => $this->executionService->generateKey(),
            'claimContext' => $claimContext,
        ];
    }

    /**
     * Handles the json field workflow.
     */
    private function jsonField(mixed $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
