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
use Catalyst\Framework\Deletion\ReverseCascadeDeleteService;
use Catalyst\Framework\Deletion\ReverseCascadeDeleteStep;

/**
 * deletion:smoke CLI command.
 *
 * Responsibility: Runs the deletion:smoke command to exercise reverse cascade delete preview, blockers and confirmed execution.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class DeletionSmokeCommand extends AbstractCommand
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
        return 'deletion:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Exercise reverse cascade delete preview, blockers and confirmed execution';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Coordinates the smoke scenario and returns a process exit code without hidden side effects.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $service = ReverseCascadeDeleteService::getInstance();

        $plan = $service->preview('documents.template', 42, [
            [
                'resource_key' => 'documents.artifacts',
                'action' => 'archive',
                'records' => [
                    ['id' => 1001, 'label' => 'artifact-1001.pdf'],
                    ['id' => 1002, 'label' => 'artifact-1002.pdf'],
                ],
            ],
            [
                'resource_key' => 'resource.attachments',
                'action' => 'detach',
                'records' => [
                    ['id' => 2001, 'label' => 'evidence'],
                ],
            ],
        ], ['smoke' => true]);

        $blockedPlan = $service->preview('documents.template', 43, [
            [
                'resource_key' => 'workflow.instances',
                'action' => 'archive',
                'block_if_present' => true,
                'records' => [
                    ['id' => 3001, 'label' => 'active approval'],
                ],
            ],
        ], ['smoke' => true]);

        $executedActions = [];
        $result = $service->execute(
            $plan,
            $plan->confirmationToken(),
            function (ReverseCascadeDeleteStep $step) use (&$executedActions): bool {
                $executedActions[] = $step->action();
                return true;
            }
        );

        $payload = [
            'success' => $plan->isExecutable()
                && !$blockedPlan->isExecutable()
                && $result->success()
                && $executedActions === ['archive', 'archive', 'detach', 'delete'],
            'plan' => $plan->toArray(),
            'blocked_plan' => $blockedPlan->toArray(),
            'result' => $result->toArray(),
        ];

        if ($json) {
            $this->line((string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->line('Reverse cascade delete smoke: ' . ($payload['success'] ? 'OK' : 'FAILED'));
            $this->line('Executable plan steps: ' . (string)$payload['plan']['step_count']);
            $this->line('Blocked plan blockers: ' . (string)$payload['blocked_plan']['blocker_count']);
            $this->line('Executed steps: ' . (string)$payload['result']['executed_count']);
        }

        return $payload['success'] ? 0 : 1;
    }
}