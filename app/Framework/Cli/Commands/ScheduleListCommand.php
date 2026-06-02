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
use Catalyst\Framework\Schedule\ScheduleRegistry;

/**
 * Defines the Schedule List Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the schedule list command behavior within its module boundary.
 */
final class ScheduleListCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render the schedule registry as JSON', false),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'schedule:list';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'List registered framework schedule tasks';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $tasks = array_values(array_map(
            static fn ($task): array => [
                'name' => $task->name,
                'expression' => $task->expression,
                'queue' => $task->queueName,
                'job_class' => $task->jobClass,
                'description' => $task->description,
            ],
            ScheduleRegistry::getInstance()->all()
        ));
        $asJson = (bool) ($args->getOptionValue('json') ?? false);

        if ($asJson) {
            $this->line((string) json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $this->line('');
        $this->info('Schedule Registry');
        $this->line(str_repeat('-', 120));
        $this->line(sprintf('  %-38s %-14s %-18s %-24s %s', 'Task', 'Expression', 'Queue', 'Job', 'Description'));
        $this->line(str_repeat('-', 120));

        foreach ($tasks as $task) {
            $this->line(sprintf(
                '  %-38s %-14s %-18s %-24s %s',
                $task['name'],
                $task['expression'],
                $task['queue'],
                mb_strimwidth($task['job_class'], 0, 24, '...'),
                $task['description']
            ));
        }

        $this->line(str_repeat('-', 120));
        $this->success(sprintf('%d scheduled task(s) listed.', count($tasks)));
        $this->line('');

        return 0;
    }
}
