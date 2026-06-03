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

namespace Catalyst\Framework\Deletion;

/**
 * Describes a safe reverse cascade delete preview.
 *
 * @package Catalyst\Framework\Deletion
 * Responsibility: Aggregates dependent delete steps, blockers and execution metadata for a root record.
 */
final readonly class ReverseCascadeDeletePlan
{
    /**
     * Stores the root record and ordered dependent delete steps.
     *
     * Responsibility: Preserves the immutable delete plan reviewed by callers before destructive execution.
     * @param ReverseCascadeDeleteStep[] $steps
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $rootResourceKey,
        private string|int $rootRecordId,
        private array $steps,
        private array $metadata = []
    ) {
    }

    /**
     * Returns planned steps in execution order.
     *
     * Responsibility: Exposes the exact ordered operations that a caller must confirm.
     * @return ReverseCascadeDeleteStep[]
     */
    public function steps(): array
    {
        return $this->steps;
    }

    /**
     * Returns blocking steps that prevent delete execution.
     *
     * Responsibility: Separates dependency blockers from executable steps for safe user confirmation.
     * @return ReverseCascadeDeleteStep[]
     */
    public function blockers(): array
    {
        return array_values(array_filter(
            $this->steps,
            static fn (ReverseCascadeDeleteStep $step): bool => $step->isBlocking()
        ));
    }

    /**
     * Reports whether the plan can be executed.
     *
     * Responsibility: Prevents execution while blocking dependencies remain in the plan.
     */
    public function isExecutable(): bool
    {
        return $this->blockers() === [];
    }

    /**
     * Returns the confirmation token required to execute this plan.
     *
     * Responsibility: Derives a deterministic confirmation boundary from root and step payloads.
     */
    public function confirmationToken(): string
    {
        return hash('sha256', $this->rootResourceKey . ':' . (string)$this->rootRecordId . ':' . count($this->steps));
    }

    /**
     * Exports the delete plan for CLI, logs or API responses.
     *
     * Responsibility: Serializes delete planning state without invoking destructive handlers.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'root_resource_key' => $this->rootResourceKey,
            'root_record_id' => $this->rootRecordId,
            'executable' => $this->isExecutable(),
            'confirmation_token' => $this->confirmationToken(),
            'step_count' => count($this->steps),
            'blocker_count' => count($this->blockers()),
            'steps' => array_map(
                static fn (ReverseCascadeDeleteStep $step): array => $step->toArray(),
                $this->steps
            ),
            'metadata' => $this->metadata,
        ];
    }
}