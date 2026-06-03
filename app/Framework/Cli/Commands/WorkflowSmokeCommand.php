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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Workflow\WorkflowDefinition;
use Catalyst\Framework\Workflow\WorkflowTransitionEvaluator;

/**
 * workflow:smoke CLI command.
 *
 * Responsibility: Runs the workflow:smoke command to exercise dynamic workflow declarations and sad-path checks.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class WorkflowSmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Exposes CLI parser metadata only; command behavior stays inside execute().
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'workflow:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Exercise dynamic workflow declarations, approvals, guards and invalid transitions';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Coordinates the smoke scenario and returns a process exit code without hidden side effects.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $definition = $this->definition();
        $evaluator = new WorkflowTransitionEvaluator();
        $abilityResolver = static fn (string $ability, mixed $record, array $context, ?array $actor): bool =>
            in_array($ability, (array) ($actor['abilities'] ?? []), true);

        $submit = $evaluator->evaluate(
            $definition,
            'draft',
            'submit-review',
            context: ['channel' => 'cli-smoke'],
            actor: ['id' => 10, 'abilities' => ['submit-review']],
            abilityResolver: $abilityResolver
        );
        $approvalMissing = $evaluator->evaluate(
            $definition,
            'in_review',
            'approve',
            actor: ['id' => 11, 'abilities' => ['approve']],
            abilityResolver: $abilityResolver
        );
        $approvalGranted = $evaluator->evaluate(
            $definition,
            'in_review',
            'approve',
            context: ['approvals' => ['academic_board' => true]],
            actor: ['id' => 11, 'abilities' => ['approve']],
            abilityResolver: $abilityResolver
        );
        $invalidTransition = $evaluator->evaluate(
            $definition,
            'draft',
            'approve',
            actor: ['id' => 11, 'abilities' => ['approve']],
            abilityResolver: $abilityResolver
        );
        $guardBlocked = $evaluator->evaluate(
            $definition,
            'approved',
            'close',
            context: ['document_ready' => false],
            actor: ['id' => 12, 'abilities' => ['close']],
            abilityResolver: $abilityResolver
        );
        $guardAllowed = $evaluator->evaluate(
            $definition,
            'approved',
            'close',
            context: ['document_ready' => true],
            actor: ['id' => 12, 'abilities' => ['close']],
            abilityResolver: $abilityResolver
        );

        $validationErrors = $definition->validate();
        $payload = [
            'success' => $validationErrors === []
                && $submit->allowed
                && !$approvalMissing->allowed
                && $approvalGranted->allowed
                && !$invalidTransition->allowed
                && !$guardBlocked->allowed
                && $guardAllowed->allowed,
            'validation_errors' => $validationErrors,
            'steps' => [
                'submit' => $submit->toArray(),
                'approval_missing' => $approvalMissing->toArray(),
                'approval_granted' => $approvalGranted->toArray(),
                'invalid_transition' => $invalidTransition->toArray(),
                'guard_blocked' => $guardBlocked->toArray(),
                'guard_allowed' => $guardAllowed->toArray(),
            ],
        ];

        if ($json) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->line('Workflow smoke: ' . ($payload['success'] ? 'OK' : 'FAILED'));
        }

        return $payload['success'] ? 0 : 1;
    }

    /**
     * Builds the reusable dynamic workflow definition exercised by the smoke command.
     *
     * Responsibility: Defines a deterministic approval workflow fixture for transition validation.
     */
    private function definition(): WorkflowDefinition
    {
        return new WorkflowDefinition(
            key: 'external-formation.lifecycle',
            resourceKey: 'external-formation-records',
            label: 'External formation lifecycle',
            initialState: 'draft',
            states: [
                'draft' => 'Draft',
                'in_review' => 'In review',
                'approved' => 'Approved',
                'returned' => 'Returned',
                'closed' => 'Closed',
                'archived' => 'Archived',
            ],
            transitions: [
                [
                    'key' => 'submit-review',
                    'label' => 'Submit for review',
                    'from' => ['draft', 'returned'],
                    'to' => 'in_review',
                    'kind' => 'submit',
                    'ability' => 'submit-review',
                ],
                [
                    'key' => 'approve',
                    'label' => 'Approve',
                    'from' => ['in_review'],
                    'to' => 'approved',
                    'kind' => 'approve',
                    'ability' => 'approve',
                    'approvals' => [
                        [
                            'key' => 'academic-board',
                            'context_key' => 'approvals.academic_board',
                            'approved_values' => [true],
                        ],
                    ],
                ],
                [
                    'key' => 'return',
                    'label' => 'Return for correction',
                    'from' => ['in_review'],
                    'to' => 'returned',
                    'kind' => 'return',
                    'ability' => 'return',
                ],
                [
                    'key' => 'close',
                    'label' => 'Close',
                    'from' => ['approved'],
                    'to' => 'closed',
                    'kind' => 'close',
                    'ability' => 'close',
                    'guard' => static fn (mixed $record, array $instance, array $context): bool|string =>
                        !empty($context['document_ready']) ? true : 'Document must be ready before closing.',
                ],
                [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'from' => ['closed'],
                    'to' => 'archived',
                    'kind' => 'archive',
                    'ability' => 'archive',
                ],
            ]
        );
    }
}