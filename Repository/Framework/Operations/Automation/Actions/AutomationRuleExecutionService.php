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
use Catalyst\Framework\Idempotency\IdempotencyManager;
use RuntimeException;

/**
 * Executes automation rules through the idempotent manual and API run flow.
 *
 * @package Catalyst\Repository\Operations\Automation\Actions
 * Responsibility: Validate execution claims and delegate idempotent rule runs to the automation manager.
 */
final class AutomationRuleExecutionService
{
    /**
     * Initializes the Automation Rule Execution Service instance.
     *
     * Responsibility: Initializes the Automation Rule Execution Service instance.
     */
    public function __construct(
        private readonly AutomationManager $manager,
        private readonly IdempotencyManager $idempotency
    ) {
    }

    /**
     * Generates an idempotency key for a new automation execution request.
     *
     * Responsibility: Generates an idempotency key for a new automation execution request.
     */
    public function generateKey(): string
    {
        return $this->idempotency->generateKey();
    }

    /**
     * Executes a rule once for the supplied context and returns the stored or replayed outcome.
     *
     * Responsibility: Executes a rule once for the supplied context and returns the stored or replayed outcome.
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
