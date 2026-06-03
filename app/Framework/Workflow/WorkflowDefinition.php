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
 * Describes workflow states, transitions and metadata for a resource.
 *
 * @package Catalyst\Framework\Workflow
 * Responsibility: Provides immutable workflow structure and transition lookups.
 */
final class WorkflowDefinition
{
    /**
     * Creates an immutable workflow definition.
     *
     * Responsibility: Binds required collaborators or immutable state without executing the main workflow.
     * @param array<string, string> $states
     * @param array<int, array<string, mixed>> $transitions
     */
    public function __construct(
        public readonly string $key,
        public readonly string $resourceKey,
        public readonly string $label,
        public readonly string $initialState,
        public readonly array $states,
        public readonly array $transitions,
        public readonly array $metadata = []
    ) {
    }

    /**
     * Returns transitions available from a state.
     *
     * Responsibility: Defines the focused behavior owned by this method and keeps side effects limited to its caller contract.
     * @return array<int, array<string, mixed>>
     */
    public function availableTransitions(string $currentState): array
    {
        return array_values(array_filter(
            $this->transitions,
            static function (array $transition) use ($currentState): bool {
                $from = $transition['from'] ?? [];
                $fromStates = is_array($from) ? array_values($from) : [(string) $from];

                return in_array($currentState, $fromStates, true);
            }
        ));
    }

    /**
     * Returns a named transition when it is valid from the current state.
     *
     * Responsibility: Coordinates the state-changing workflow after validation and returns the outcome to the caller.
     * @return array<string, mixed>|null
     */
    public function transition(string $transitionKey, string $currentState): ?array
    {
        foreach ($this->availableTransitions($currentState) as $transition) {
            if ((string) ($transition['key'] ?? '') === $transitionKey) {
                return $transition;
            }
        }

        return null;
    }

    /**
     * Validates state, transition and approval declaration coherence.
     *
     * Responsibility: Enforces framework invariants before data crosses into persistence, execution or rendering boundaries.
     * @return array<int, string>
     */
    public function validate(): array
    {
        $errors = [];
        if (!isset($this->states[$this->initialState])) {
            $errors[] = sprintf('Initial state "%s" is not declared.', $this->initialState);
        }

        $seenTransitions = [];
        foreach ($this->transitions as $index => $transition) {
            $transitionKey = trim((string) ($transition['key'] ?? ''));
            if ($transitionKey === '') {
                $errors[] = sprintf('Transition at index %d is missing key.', $index);
                continue;
            }

            if (isset($seenTransitions[$transitionKey])) {
                $errors[] = sprintf('Transition "%s" is declared more than once.', $transitionKey);
            }
            $seenTransitions[$transitionKey] = true;

            $from = $transition['from'] ?? [];
            $fromStates = is_array($from) ? array_values($from) : [(string) $from];
            if ($fromStates === []) {
                $errors[] = sprintf('Transition "%s" has no source states.', $transitionKey);
            }

            foreach ($fromStates as $state) {
                if (!isset($this->states[(string) $state])) {
                    $errors[] = sprintf('Transition "%s" references unknown source state "%s".', $transitionKey, (string) $state);
                }
            }

            $to = (string) ($transition['to'] ?? '');
            if ($to === '' || !isset($this->states[$to])) {
                $errors[] = sprintf('Transition "%s" references unknown target state "%s".', $transitionKey, $to);
            }

            foreach ($this->approvalRequirements($transition) as $approval) {
                $approvalKey = trim((string) ($approval['key'] ?? ''));
                $contextKey = trim((string) ($approval['context_key'] ?? ''));
                if ($approvalKey === '' || $contextKey === '') {
                    $errors[] = sprintf('Transition "%s" has an invalid approval requirement.', $transitionKey);
                }
            }
        }

        return $errors;
    }

    /**
     * Returns the semantic transition type used by app screens and APIs.
     *
     * Responsibility: Defines the focused behavior owned by this method and keeps side effects limited to its caller contract.
     */
    public function transitionKind(array $transition): string
    {
        return trim((string) ($transition['kind'] ?? 'custom')) ?: 'custom';
    }

    /**
     * Returns normalized approval requirements for a transition.
     *
     * Responsibility: Defines the focused behavior owned by this method and keeps side effects limited to its caller contract.
     * @return array<int, array<string, mixed>>
     */
    public function approvalRequirements(array $transition): array
    {
        $approvals = $transition['approvals'] ?? [];
        if (is_array($approvals) && $approvals !== []) {
            return array_values(array_filter($approvals, 'is_array'));
        }

        if (!empty($transition['requires_approval'])) {
            $transitionKey = trim((string) ($transition['key'] ?? 'transition'));

            return [[
                'key' => $transitionKey,
                'context_key' => 'approvals.' . $transitionKey,
                'approved_values' => [true, 'approved', '1', 1],
            ]];
        }

        return [];
    }
}