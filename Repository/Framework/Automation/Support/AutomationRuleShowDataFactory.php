<?php

declare(strict_types=1);

namespace Catalyst\Repository\Automation\Support;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Automation\AutomationRuleRepository;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;
use Catalyst\Repository\Automation\Actions\AutomationRuleExecutionService;

final class AutomationRuleShowDataFactory
{
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

    private function jsonField(mixed $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
