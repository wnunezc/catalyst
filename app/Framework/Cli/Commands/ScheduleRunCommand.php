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
use Catalyst\Framework\Schedule\ScheduleRunner;
use Throwable;

/**
 * schedule:run CLI command.
 *
 * Responsibility: Runs the schedule:run command to Evaluate the schedule registry and queue due tasks.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ScheduleRunCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option('t', 'task', null, false, 'Run only one registered task by name', true),
            new Option(null, 'force', false, false, 'Run matching task(s) even when not due', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'schedule:run';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Evaluate the schedule registry and queue due tasks';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
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
