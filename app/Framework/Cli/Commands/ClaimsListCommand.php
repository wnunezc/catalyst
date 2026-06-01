<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Concurrency\RecordClaimRepository;

final class ClaimsListCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'resource', '', false, 'Filter by resource key', true),
            new Option(null, 'record-id', null, false, 'Filter by record ID', true),
            new Option(null, 'actor-id', null, false, 'Filter by actor ID', true),
            new Option(null, 'active', false, false, 'List only active claims', false),
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'claims:list';
    }

    public function getDescription(): string
    {
        return 'List reusable record claims with current status';
    }

    public function execute(ArgumentBag $args): int
    {
        $rows = RecordClaimRepository::getInstance()->search([
            'resource_key' => (string) ($args->getOptionValue('resource') ?? ''),
            'record_id' => (int) ($args->getOptionValue('record-id') ?? 0),
            'actor_id' => (int) ($args->getOptionValue('actor-id') ?? 0),
            'active' => (bool) ($args->getOptionValue('active') ?? false),
        ]);

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode(['rows' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info('Record Claims');
        $this->line(str_repeat('-', 118));
        $this->line(sprintf(
            '  %-28s %-10s %-18s %-10s %-19s %s',
            'Resource',
            'Record',
            'Claimant',
            'Status',
            'Expires At',
            'Release Reason'
        ));
        $this->line(str_repeat('-', 118));

        foreach ($rows as $row) {
            $this->line(sprintf(
                '  %-28s %-10s %-18s %-10s %-19s %s',
                substr((string) ($row['resource_key'] ?? ''), 0, 28),
                (string) ($row['record_id'] ?? ''),
                substr((string) ($row['claimed_by_label'] ?? ''), 0, 18),
                (string) ($row['status'] ?? ''),
                (string) ($row['expires_at'] ?? '-'),
                substr((string) ($row['release_reason'] ?? ''), 0, 30)
            ));
        }

        $this->line(str_repeat('-', 118));
        $this->success(sprintf('%d claim(s) listed.', count($rows)));
        $this->line('');

        return 0;
    }
}
