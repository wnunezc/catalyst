<?php

declare(strict_types=1);

namespace Catalyst\Framework\Workflow;

final class WorkflowDefinition
{
    /**
     * @param array<string, string> $states
     * @param array<int, array<string, mixed>> $transitions
     */
    public function __construct(
        public readonly string $key,
        public readonly string $resourceKey,
        public readonly string $label,
        public readonly string $initialState,
        public readonly array $states,
        public readonly array $transitions
    ) {
    }

    /**
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
}
