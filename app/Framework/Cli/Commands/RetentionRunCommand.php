<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Retention\RetentionManager;

final class RetentionRunCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'resource', '', false, 'Optional policy resource_key filter', true),
            new Option(null, 'limit', 100, false, 'Maximum number of candidates to inspect', true),
            new Option(null, 'dry-run', false, false, 'Inspect candidates without mutating them', false),
            new Option(null, 'list-policies', false, false, 'List canonical retention policies', false),
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'retention:run';
    }

    public function getDescription(): string
    {
        return 'Inspect or execute canonical PA-05 retention / archive / purge policies';
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = RetentionManager::getInstance();
        $json = (bool) ($args->getOptionValue('json') ?? false);

        if ((bool) ($args->getOptionValue('list-policies') ?? false)) {
            $payload = $manager->policies();

            if ($json) {
                $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                return 0;
            }

            $this->line('');
            $this->info('Retention Policies');
            $this->line('');

            foreach ($payload as $policy) {
                $this->line(sprintf(
                    '  %-20s archive=%-6s purge=%-6s %s',
                    (string) ($policy['resource_key'] ?? ''),
                    (string) (($policy['archive_after_days'] ?? null) === null ? '-' : $policy['archive_after_days']),
                    (string) (($policy['purge_after_days'] ?? null) === null ? '-' : $policy['purge_after_days']),
                    (string) ($policy['mode'] ?? '')
                ));
            }

            $this->line('');

            return 0;
        }

        $result = $manager->run(
            resourceKey: trim((string) ($args->getOptionValue('resource') ?? '')) ?: null,
            dryRun: (bool) ($args->getOptionValue('dry-run') ?? false),
            limit: max(1, (int) ($args->getOptionValue('limit') ?? 100))
        );

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info(!empty($result['dry_run']) ? 'Retention Dry Run' : 'Retention Run');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-18s %-8s #%d %s',
                (string) ($step['resource_key'] ?? ''),
                strtoupper((string) ($step['action'] ?? '')),
                (int) ($step['record_id'] ?? 0),
                (string) ($step['label'] ?? '')
            ));
        }

        $this->line('');

        return 0;
    }
}
