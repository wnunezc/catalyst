<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Schedule\ScheduleRunner;
use Throwable;

final class ScheduleRunCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option('t', 'task', null, false, 'Run only one registered task by name', true),
            new Option(null, 'force', false, false, 'Run matching task(s) even when not due', false),
        ];
    }

    public function getName(): string
    {
        return 'schedule:run';
    }

    public function getDescription(): string
    {
        return 'Evaluate the schedule registry and queue due tasks';
    }

    public function execute(ArgumentBag $args): int
    {
        $task = trim((string) ($args->getOptionValue('task') ?? $args->getOptionValue('t') ?? ''));
        $force = (bool) ($args->getOptionValue('force') ?? false);

        try {
            $results = (new ScheduleRunner())->run($task !== '' ? $task : null, $force);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->line('');
        $this->info('Schedule Runner');
        $this->line(str_repeat('-', 80));

        foreach ($results as $result) {
            $line = sprintf(
                '%-42s %-10s %s',
                $result['task'],
                strtoupper($result['status']),
                $result['message'] ?? ''
            );

            if ($result['status'] === 'queued') {
                $this->success($line . (isset($result['job_id']) ? ' (job #' . $result['job_id'] . ')' : ''));
                continue;
            }

            if ($result['status'] === 'skipped' || $result['status'] === 'locked') {
                $this->warn($line);
                continue;
            }

            $this->line($line);
        }

        $this->line(str_repeat('-', 80));
        $this->line('');

        return 0;
    }
}
