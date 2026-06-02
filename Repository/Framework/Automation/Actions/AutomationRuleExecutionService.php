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

namespace Catalyst\Repository\Automation\Actions;

use Catalyst\Entities\AutomationRule;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Idempotency\IdempotencyManager;
use RuntimeException;

/**
 * Defines the Automation Rule Execution Service class contract.
 *
 * @package Catalyst\Repository\Automation\Actions
 * Responsibility: Coordinates the automation rule execution service behavior within its module boundary.
 */
final class AutomationRuleExecutionService
{
    /**
     * Initializes the Automation Rule Execution Service instance.
     */
    public function __construct(
        private readonly AutomationManager $manager,
        private readonly IdempotencyManager $idempotency
    ) {
    }

    /**
     * Handles the generate key workflow.
     */
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
