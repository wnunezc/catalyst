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

namespace Catalyst\Framework\Workflow;

/**
 * Evaluates workflow transitions without mutating storage.
 *
 * @package Catalyst\Framework\Workflow
 * Responsibility: Applies transition availability, permission, guard and approval checks before persistence.
 */
final class WorkflowTransitionEvaluator
{
    /**
     * Evaluates whether a transition can run from the current state.
     *
     * Responsibility: Applies state, role, permission and approval rules before a workflow transition executes.
     * @param array<string, mixed> $context
     * @param array<string, mixed>|null $actor
     * @param callable|null $abilityResolver
     */
    public function evaluate(
        WorkflowDefinition $definition,
        string $currentState,
        string $transitionKey,
        mixed $record = null,
        array $context = [],
        ?array $actor = null,
        ?callable $abilityResolver = null,
        array $workflowInstance = []
    ): WorkflowTransitionDecision {
        $transition = $definition->transition($transitionKey, $currentState);
        if ($transition === null) {
            return new WorkflowTransitionDecision(
                false,
                sprintf('Transition "%s" is not available from state "%s".', $transitionKey, $currentState),
                $currentState,
                $currentState
            );
        }

        $targetState = (string) ($transition['to'] ?? $currentState);
        $ability = trim((string) ($transition['ability'] ?? ''));
        if ($ability !== '' && empty($context['system'])) {
            $allowedByAbility = $abilityResolver === null
                ? true
                : (bool) $abilityResolver($ability, $record, $context, $actor, $transition);
            if (!$allowedByAbility) {
                return new WorkflowTransitionDecision(
                    false,
                    sprintf('Ability "%s" is required for transition "%s".', $ability, $transitionKey),
                    $currentState,
                    $targetState,
                    $transition
                );
            }
        }

        $guard = $transition['guard'] ?? null;
        if (is_callable($guard)) {
            $guardResult = $guard($record, $workflowInstance !== [] ? $workflowInstance : ['current_state' => $currentState], $context, $actor);
            if ($guardResult === false) {
                return new WorkflowTransitionDecision(false, 'Workflow guard blocked the transition.', $currentState, $targetState, $transition);
            }
            if (is_string($guardResult) && trim($guardResult) !== '') {
                return new WorkflowTransitionDecision(false, $guardResult, $currentState, $targetState, $transition);
            }
        }

        foreach ($definition->approvalRequirements($transition) as $approval) {
            if (!$this->approvalSatisfied($approval, $context)) {
                return new WorkflowTransitionDecision(
                    false,
                    sprintf('Approval "%s" is required for transition "%s".', (string) ($approval['key'] ?? 'approval'), $transitionKey),
                    $currentState,
                    $targetState,
                    $transition
                );
            }
        }

        return new WorkflowTransitionDecision(true, 'allowed', $currentState, $targetState, $transition);
    }

    /**
     * Determines whether one approval requirement is satisfied by context.
     *
     * Responsibility: Checks approval context for a single requirement without mutating workflow state.
     * @param array<string, mixed> $approval
     * @param array<string, mixed> $context
     */
    private function approvalSatisfied(array $approval, array $context): bool
    {
        $contextKey = trim((string) ($approval['context_key'] ?? ''));
        if ($contextKey === '') {
            return false;
        }

        $value = $this->contextValue($context, $contextKey);
        $approvedValues = $approval['approved_values'] ?? [true, 'approved', '1', 1];
        $approvedValues = is_array($approvedValues) ? $approvedValues : [$approvedValues];

        foreach ($approvedValues as $approvedValue) {
            if ($value === $approvedValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reads nested context values using dot notation.
     *
     * Responsibility: Resolves workflow condition data from nested runtime context arrays.
     * @param array<string, mixed> $context
     */
    private function contextValue(array $context, string $path): mixed
    {
        $value = $context;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}