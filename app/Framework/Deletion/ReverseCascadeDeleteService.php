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

use Catalyst\Framework\Traits\SingletonTrait;
use InvalidArgumentException;

/**
 * Builds and executes explicit reverse cascade delete plans.
 *
 * @package Catalyst\Framework\Deletion
 * Responsibility: Provides dry-run, blocker and confirmed execution boundaries for dependent record deletion.
 */
final class ReverseCascadeDeleteService
{
    use SingletonTrait;

    /**
     * Actions accepted by the reverse cascade delete contract.
     *
     * @var string[]
     */
    private const ALLOWED_ACTIONS = ['archive', 'detach', 'delete', 'soft-delete'];

    /**
     * Builds a delete preview from declared dependency records.
     *
     * Responsibility: Normalizes declared dependencies into a reviewable plan before any destructive action is possible.
     * @param array<int, array<string, mixed>> $dependencies
     * @param array<string, mixed> $metadata
     */
    public function preview(
        string $rootResourceKey,
        string|int $rootRecordId,
        array $dependencies,
        array $metadata = []
    ): ReverseCascadeDeletePlan {
        $steps = [];

        foreach ($dependencies as $dependency) {
            $resourceKey = trim((string)($dependency['resource_key'] ?? ''));
            $action = trim((string)($dependency['action'] ?? ''));
            $records = $dependency['records'] ?? [];
            $blockIfPresent = (bool)($dependency['block_if_present'] ?? false);

            if ($resourceKey === '') {
                throw new InvalidArgumentException('Reverse cascade dependency requires resource_key.');
            }

            if (!in_array($action, self::ALLOWED_ACTIONS, true)) {
                throw new InvalidArgumentException('Reverse cascade dependency action is not allowed.');
            }

            if (!is_array($records)) {
                throw new InvalidArgumentException('Reverse cascade dependency records must be an array.');
            }

            foreach ($records as $record) {
                if (!is_array($record)) {
                    throw new InvalidArgumentException('Reverse cascade dependency record must be an array.');
                }

                $steps[] = ReverseCascadeDeleteStep::fromRecord($resourceKey, $action, $record, $blockIfPresent);
            }
        }

        $steps[] = new ReverseCascadeDeleteStep($rootResourceKey, $rootRecordId, 'delete', 'root');

        return new ReverseCascadeDeletePlan($rootResourceKey, $rootRecordId, $steps, $metadata);
    }

    /**
     * Executes a confirmed plan through a caller-provided action handler.
     *
     * Responsibility: Enforces blocker and token checks before delegating each planned operation to application code.
     * @param callable(ReverseCascadeDeleteStep): bool $handler
     */
    public function execute(ReverseCascadeDeletePlan $plan, string $confirmationToken, callable $handler): ReverseCascadeDeleteResult
    {
        if (!$plan->isExecutable()) {
            return new ReverseCascadeDeleteResult(false, 'Plan contains blocking dependencies.');
        }

        if (!hash_equals($plan->confirmationToken(), $confirmationToken)) {
            return new ReverseCascadeDeleteResult(false, 'Confirmation token mismatch.');
        }

        $executed = [];
        foreach ($plan->steps() as $step) {
            if ($handler($step) !== true) {
                return new ReverseCascadeDeleteResult(false, 'Delete handler rejected a planned step.', $executed);
            }

            $executed[] = $step->toArray();
        }

        return new ReverseCascadeDeleteResult(true, 'Reverse cascade delete plan executed.', $executed);
    }
}