<?php

declare(strict_types=1);

namespace Catalyst\Repository\Automation\Actions;

use Catalyst\Entities\AutomationRule;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

final class AutomationRuleMutationService
{
    private Logger $logger;

    public function __construct(
        private readonly AutomationManager $manager
    ) {
        $this->logger = Logger::getInstance();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(AutomationRule $rule, Request $request, array $payload): void
    {
        $ruleId = (int) $rule->getKey();
        $this->assertClaim($ruleId, $request);

        $rule->fill([
            'lock_version' => max(1, (int) $request->input('lock_version', $rule->toArray()['lock_version'] ?? 1)),
        ]);

        $this->manager->update($rule, $payload);
        $this->releaseClaim($ruleId, $request, 'automation rule updated');
    }

    public function delete(AutomationRule $rule, Request $request): void
    {
        $ruleId = (int) $rule->getKey();
        $this->assertClaim($ruleId, $request);
        $rule->delete();
        $this->releaseClaim($ruleId, $request, 'automation rule deleted');
    }

    public function transition(AutomationRule $rule, Request $request, string $transition, ?string $notes): void
    {
        $this->assertClaim((int) $rule->getKey(), $request);
        $this->manager->transition($rule, $transition, $notes);
    }

    public function restoreVersion(AutomationRule $rule, Request $request, int $versionId): void
    {
        $ruleId = (int) $rule->getKey();
        $this->assertClaim($ruleId, $request);
        $versionManager = VersionManager::getInstance();
        $restored = $versionManager->restore($versionId);
        $versionManager->capture(
            AutomationManager::RESOURCE_KEY,
            $ruleId,
            $restored,
            __('automation.messages.restored_summary') . ' ' . $versionId
        );
    }

    private function assertClaim(int $ruleId, Request $request): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        RecordClaimManager::getInstance()->assertAvailable(
            resourceKey: AutomationManager::RESOURCE_KEY,
            recordId: $ruleId,
            claimToken: $claimToken !== '' ? $claimToken : null
        );
    }

    private function releaseClaim(int $ruleId, Request $request, ?string $reason = null): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        try {
            RecordClaimManager::getInstance()->release(
                resourceKey: AutomationManager::RESOURCE_KEY,
                recordId: $ruleId,
                reason: $reason,
                claimToken: $claimToken !== '' ? $claimToken : null
            );
        } catch (Throwable $e) {
            $this->logger->warning('Automation claim release skipped after mutation.', [
                'record_id' => $ruleId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
