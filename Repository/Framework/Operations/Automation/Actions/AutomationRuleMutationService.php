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

namespace Catalyst\Repository\Operations\Automation\Actions;

use Catalyst\Entities\AutomationRule;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

/**
 * Applies claim-protected mutations to automation rules.
 *
 * @package Catalyst\Repository\Operations\Automation\Actions
 * Responsibility: Update, delete, transition and restore automation rules while enforcing record claims.
 */
final class AutomationRuleMutationService
{
    private Logger $logger;

    /**
     * Initializes the Automation Rule Mutation Service instance.
     *
     * Responsibility: Initializes the Automation Rule Mutation Service instance.
     */
    public function __construct(
        private readonly AutomationManager $manager
    ) {
        $this->logger = Logger::getInstance();
    }

    /**
     * Updates an automation rule and releases its record claim after persistence.
     *
     * Responsibility: Updates an automation rule and releases its record claim after persistence.
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

    /**
     * Deletes an automation rule and releases its record claim.
     *
     * Responsibility: Deletes an automation rule and releases its record claim.
     */
    public function delete(AutomationRule $rule, Request $request): void
    {
        $ruleId = (int) $rule->getKey();
        $this->assertClaim($ruleId, $request);
        $rule->delete();
        $this->releaseClaim($ruleId, $request, 'automation rule deleted');
    }

    /**
     * Applies a workflow transition to an automation rule after claim validation.
     *
     * Responsibility: Applies a workflow transition to an automation rule after claim validation.
     */
    public function transition(AutomationRule $rule, Request $request, string $transition, ?string $notes): void
    {
        $this->assertClaim((int) $rule->getKey(), $request);
        $this->manager->transition($rule, $transition, $notes);
    }

    /**
     * Restores a captured automation rule version after claim validation.
     *
     * Responsibility: Restores a captured automation rule version after claim validation.
     */
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

    /**
     * Verifies that the request holds an available claim for the automation rule.
     *
     * Responsibility: Verifies that the request holds an available claim for the automation rule.
     */
    private function assertClaim(int $ruleId, Request $request): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        RecordClaimManager::getInstance()->assertAvailable(
            resourceKey: AutomationManager::RESOURCE_KEY,
            recordId: $ruleId,
            claimToken: $claimToken !== '' ? $claimToken : null
        );
    }

    /**
     * Releases the request claim after a successful mutation without masking persistence success.
     *
     * Responsibility: Releases the request claim after a successful mutation without masking persistence success.
     */
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
