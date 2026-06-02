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
use Catalyst\Framework\Reporting\ReportingManager;

/**
 * Defines the Reporting Run Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the reporting run command behavior within its module boundary.
 */
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

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'reporting:run';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Queue a canonical PA-10 report run against the reusable reporting pipeline';
    }

    /**
     * Executes the service workflow.
     */
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
