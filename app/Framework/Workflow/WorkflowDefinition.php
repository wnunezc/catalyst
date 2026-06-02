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
 * Defines the Workflow Definition class contract.
 *
 * @package Catalyst\Framework\Workflow
 * Responsibility: Coordinates the workflow definition behavior within its module boundary.
 */
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
