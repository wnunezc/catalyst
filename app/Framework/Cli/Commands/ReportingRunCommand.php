<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Reporting\ReportingManager;

final class ReportingRunCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'report', 'framework.attachments.by-resource', false, 'Report definition key', true),
            new Option(null, 'resource', '', false, 'Target resource key for report criteria', true),
            new Option(null, 'record-id', 0, false, 'Target record ID for report criteria', true),
            new Option(null, 'include-detached', false, false, 'Include detached attachments in the report', false),
            new Option(null, 'attach-resource', '', false, 'Resource key that should receive the output attachment', true),
            new Option(null, 'attach-record-id', 0, false, 'Record ID that should receive the output attachment', true),
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'reporting:run';
    }

    public function getDescription(): string
    {
        return 'Queue a canonical PA-10 report run against the reusable reporting pipeline';
    }

    public function execute(ArgumentBag $args): int
    {
        $run = ReportingManager::getInstance()->queue(
            reportKey: trim((string) ($args->getOptionValue('report') ?? 'framework.attachments.by-resource')),
            criteria: [
                'resource_key' => trim((string) ($args->getOptionValue('resource') ?? '')),
                'record_id' => (int) ($args->getOptionValue('record-id') ?? 0),
                'include_detached' => (bool) ($args->getOptionValue('include-detached') ?? false),
            ],
            attachTo: trim((string) ($args->getOptionValue('attach-resource') ?? '')) !== ''
                ? [
                    'resource_key' => trim((string) ($args->getOptionValue('attach-resource') ?? '')),
                    'record_id' => (int) ($args->getOptionValue('attach-record-id') ?? 0),
                ]
                : null
        );

        $payload = $run->toArray();

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->success(sprintf(
            'Report run %d queued on job %d.',
            (int) ($payload['id'] ?? 0),
            (int) ($payload['queued_job_id'] ?? 0)
        ));
        $this->line('');

        return 0;
    }
}
