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
use Catalyst\Framework\Queue\QueueWorker;

/**
 * queue:work CLI command.
 *
 * Responsibility: Runs the queue:work command to Process queued jobs from the framework queue backend.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class QueueWorkCommand extends AbstractCommand
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
            new Option('q', 'queue', null, false, 'Queue name to process', true),
            new Option('m', 'max-jobs', 1, false, 'Maximum jobs to process before exiting', true),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'queue:work';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Process queued jobs from the framework queue backend';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $queue = trim((string) ($args->getOptionValue('queue') ?? $args->getOptionValue('q') ?? ''));
        $maxJobs = max(1, (int) ($args->getOptionValue('max-jobs') ?? $args->getOptionValue('m') ?? 1));
        $worker = new QueueWorker();

        $processed = 0;
        $failed = 0;

        $this->line('');
        $this->info('Queue Worker');
        $this->line(str_repeat('-', 50));

        for ($index = 0; $index < $maxJobs; $index++) {
            $result = $worker->processNext($queue !== '' ? $queue : null);

            if ($result['status'] === 'idle') {
                $this->warn($result['message'] ?? 'No queued jobs are ready.');
                break;
            }

            $label = sprintf('[#%d] %s', (int) ($result['job_id'] ?? 0), (string) ($result['display_name'] ?? 'job'));

            if ($result['status'] === 'processed') {
                $processed++;
                $this->success($label . ' processed');
                continue;
            }

            if ($result['status'] === 'released') {
                $this->warn($label . ' released for retry — ' . ($result['message'] ?? 'unknown error'));
                continue;
            }

            if ($result['status'] === 'failed') {
                $failed++;
                $this->error($label . ' moved to failed jobs — ' . ($result['message'] ?? 'unknown error'));
            }
        }

        $this->line(str_repeat('-', 50));
        $this->line(sprintf('Processed: %d | Failed: %d', $processed, $failed));
        $this->line('');

        return $failed > 0 ? 1 : 0;
    }
}
