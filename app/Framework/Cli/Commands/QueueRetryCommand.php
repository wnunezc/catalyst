<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Queue\QueueRepository;

final class QueueRetryCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'all', false, false, 'Retry all failed jobs', false),
        ];
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(0, null, false, null, 'FailedJobId', 'Failed job ID to retry'),
        ];
    }

    public function getName(): string
    {
        return 'queue:retry';
    }

    public function getDescription(): string
    {
        return 'Retry one failed job or all failed jobs';
    }

    public function execute(ArgumentBag $args): int
    {
        $retryAll = (bool) ($args->getOptionValue('all') ?? false);
        $failedId = $args->getParameterValue(0);

        if (!$retryAll && ($failedId === null || trim((string) $failedId) === '')) {
            $this->error('Provide a failed job ID or pass --all.');
            return 1;
        }

        $requeued = QueueRepository::getInstance()->retryFailed(
            $retryAll ? null : (int) $failedId
        );

        $this->line('');

        if ($requeued === []) {
            $this->warn('No failed jobs were retried.');
            $this->line('');
            return 0;
        }

        $this->success(sprintf('%d failed job(s) re-queued.', count($requeued)));
        $this->line('New queue job IDs: ' . implode(', ', $requeued));
        $this->line('');

        return 0;
    }
}
