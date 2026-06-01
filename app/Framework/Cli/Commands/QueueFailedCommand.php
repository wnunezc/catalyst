<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Queue\QueueRepository;

final class QueueFailedCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option('l', 'limit', 20, false, 'Maximum failed jobs to show', true),
            new Option('q', 'queue', null, false, 'Filter by queue name', true),
            new Option(null, 'json', false, false, 'Render failed jobs as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'queue:failed';
    }

    public function getDescription(): string
    {
        return 'List failed jobs persisted by the framework queue';
    }

    public function execute(ArgumentBag $args): int
    {
        $limit = max(1, (int) ($args->getOptionValue('limit') ?? $args->getOptionValue('l') ?? 20));
        $queue = trim((string) ($args->getOptionValue('queue') ?? $args->getOptionValue('q') ?? ''));
        $asJson = (bool) ($args->getOptionValue('json') ?? false);
        $rows = QueueRepository::getInstance()->listFailed($limit, $queue !== '' ? $queue : null);

        if ($asJson) {
            $this->line((string) json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $this->line('');
        $this->info('Failed Jobs');
        $this->line(str_repeat('-', 120));
        $this->line(sprintf('  %-6s %-18s %-28s %-10s %-20s %s', 'ID', 'Queue', 'Display', 'Attempts', 'Failed At', 'Error'));
        $this->line(str_repeat('-', 120));

        if ($rows === []) {
            $this->warn('No failed jobs found.');
            $this->line('');
            return 0;
        }

        foreach ($rows as $row) {
            $this->line(sprintf(
                '  %-6s %-18s %-28s %-10s %-20s %s',
                (string) ($row['id'] ?? '-'),
                (string) ($row['queue_name'] ?? '-'),
                mb_strimwidth((string) ($row['display_name'] ?? '-'), 0, 28, '...'),
                sprintf('%d/%d', (int) ($row['attempts'] ?? 0), (int) ($row['max_attempts'] ?? 0)),
                (string) ($row['failed_at'] ?? '-'),
                mb_strimwidth((string) ($row['error_message'] ?? ''), 0, 40, '...')
            ));
        }

        $this->line(str_repeat('-', 120));
        $this->success(sprintf('%d failed job(s) listed.', count($rows)));
        $this->line('');

        return 0;
    }
}
