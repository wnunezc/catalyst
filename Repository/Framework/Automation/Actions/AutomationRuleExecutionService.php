<?php

declare(strict_types=1);

namespace Catalyst\Repository\Automation\Actions;

use Catalyst\Entities\AutomationRule;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Idempotency\IdempotencyManager;
use RuntimeException;

final class AutomationRuleExecutionService
{
    public function __construct(
        private readonly AutomationManager $manager,
        private readonly IdempotencyManager $idempotency
    ) {
    }

    public function generateKey(): string
    {
        return $this->idempotency->generateKey();
    }

    /**
     * @param array<string, mixed> $context
     * @return array{replayed:bool,outcome:array<string, mixed>}
     */
    public function execute(Request $request, AutomationRule $rule, array $context, string $triggerSource, bool $assertClaim): array
    {
        if ($assertClaim) {
            $claimToken = trim((string) $request->input('claim_token', ''));
            RecordClaimManager::getInstance()->assertAvailable(
                resourceKey: AutomationManager::RESOURCE_KEY,
                recordId: (int) $rule->getKey(),
                claimToken: $claimToken !== '' ? $claimToken : null
            );
        }

        $idempotencyKey = $request->idempotencyKey();
        if ($idempotencyKey === '') {
            throw new RuntimeException(__('automation.messages.idempotency_required'));
        }

        return $this->idempotency->execute(
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
}
