<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Schedule\ScheduleRegistry;

final class ScheduleListCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render the schedule registry as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'schedule:list';
    }

    public function getDescription(): string
    {
        return 'List registered framework schedule tasks';
    }

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
