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

namespace Catalyst\Framework\Schedule;

use Catalyst\Entities\ScheduledTask;
use Catalyst\Framework\Event\EventBus;
use Catalyst\Framework\Queue\QueueManager;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

/**
 * Evaluates scheduled tasks and dispatches due work to the queue.
 *
 * @package Catalyst\Framework\Schedule
 * Responsibility: Resolves task scope, checks cron slots, prevents duplicate execution, queues due jobs, and reports outcomes.
 */
final class ScheduleRunner
{
    /**
     * Initializes the Schedule Runner instance.
     *
     * Responsibility: Initializes the Schedule Runner instance.
     */
    public function __construct(
        private readonly ScheduleLockManager $lockManager = new ScheduleLockManager()
    ) {
    }

    /**
     * Evaluates due tasks or one requested task and returns dispatch outcomes.
     *
     * Responsibility: Evaluates due tasks or one requested task and returns dispatch outcomes.
     * @return array<int, array{task:string,status:string,job_id?:int,message?:string}>
     */
    public function run(?string $taskName = null, bool $force = false): array
    {
        $tasks = $taskName !== null && $taskName !== ''
            ? [$this->resolveTask($taskName)]
            : array_values(ScheduleRegistry::getInstance()->all());

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $results = [];

        foreach ($tasks as $task) {
            $isDue = CronExpression::isDue($task->expression, $now);

            if (!$force && !$isDue) {
                $results[] = [
                    'task' => $task->name,
                    'status' => 'skipped',
                    'message' => 'Task is not due in the current slot.',
                ];
                continue;
            }

            $slotKey = $force ? $now->format('YmdHis') : $now->format('YmdHi');

            try {
                $result = $this->lockManager->runWithLock($task->name, function () use ($task, $slotKey): array {
                    if (!$this->claimTaskSlot($task, $slotKey)) {
                        return [
                            'task' => $task->name,
                            'status' => 'skipped',
                            'message' => 'Task already claimed for this slot.',
                        ];
                    }

                    $jobId = QueueManager::getInstance()->dispatch($task->makeJob(), queueName: $task->queueName);
                    ScheduleRepository::getInstance()->markQueued($task->name, $slotKey, $jobId, 'Task queued by scheduler.');

                    EventBus::getInstance()->dispatch('framework.schedule.task-queued', [
                        'task_name' => $task->name,
                        'expression' => $task->expression,
                        'slot_key' => $slotKey,
                        'queue_name' => $task->queueName,
                        'job_id' => $jobId,
                    ]);

                    return [
                        'task' => $task->name,
                        'status' => 'queued',
                        'job_id' => $jobId,
                        'message' => 'Task queued successfully.',
                    ];
                });

                $results[] = $result;
            } catch (Throwable $e) {
                $results[] = [
                    'task' => $task->name,
                    'status' => 'locked',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Resolves a registered scheduled task or fails for an unknown name.
     *
     * Responsibility: Resolves a registered scheduled task or fails for an unknown name.
     */
    private function resolveTask(string $taskName): ScheduledTask
    {
        $task = ScheduleRegistry::getInstance()->get($taskName);

        if ($task === null) {
            throw new RuntimeException("Scheduled task '{$taskName}' is not registered.");
        }

        return $task;
    }

    /**
     * Claims the task slot before queue dispatch to prevent duplicate runs.
     *
     * Responsibility: Claims the task slot before queue dispatch to prevent duplicate runs.
     */
    private function claimTaskSlot(ScheduledTask $task, string $slotKey): bool
    {
        return ScheduleRepository::getInstance()->claimSlot(
            taskName: $task->name,
            expression: $task->expression,
            slotKey: $slotKey,
            queueName: $task->queueName
        );
    }
}
