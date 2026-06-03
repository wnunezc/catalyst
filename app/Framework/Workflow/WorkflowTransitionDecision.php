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
 * Carries the result of evaluating a workflow transition.
 *
 * @package Catalyst\Framework\Workflow
 * Responsibility: Carries transition availability, target state and denial reason without mutating persistence.
 */
final class WorkflowTransitionDecision
{
    /**
     * Creates a workflow transition decision value object.
     *
     * Responsibility: Stores transition authorization outcome, target state and denial reasons.
     * @param array<string, mixed>|null $transition
     */
    public function __construct(
        public readonly bool $allowed,
        public readonly string $reason,
        public readonly string $fromState,
        public readonly string $toState,
        public readonly ?array $transition = null
    ) {
    }

    /**
     * Returns a serializable representation for CLI smoke checks and APIs.
     *
     * Responsibility: Formats transition decisions without re-evaluating workflow rules.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'allowed' => $this->allowed,
            'reason' => $this->reason,
            'from_state' => $this->fromState,
            'to_state' => $this->toState,
            'transition_key' => $this->transition['key'] ?? null,
            'kind' => $this->transition['kind'] ?? 'custom',
        ];
    }
}