<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Queue\QueueWorker;

final class QueueWorkCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option('q', 'queue', null, false, 'Queue name to process', true),
            new Option('m', 'max-jobs', 1, false, 'Maximum jobs to process before exiting', true),
        ];
    }

    public function getName(): string
    {
        return 'queue:work';
    }

    public function getDescription(): string
    {
        return 'Process queued jobs from the framework queue backend';
    }

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
