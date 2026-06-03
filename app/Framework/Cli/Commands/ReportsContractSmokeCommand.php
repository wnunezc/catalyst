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
use Catalyst\Framework\Reporting\DataGridReportExporter;
use Catalyst\Framework\Reporting\ReportDefinition;
use Catalyst\Framework\Reporting\ReportFormat;
use Catalyst\Framework\Reporting\ReportProviderInterface;
use Catalyst\Framework\Reporting\ReportProviderRegistry;
use Catalyst\Framework\Reporting\ReportingManager;
use Catalyst\Framework\Reporting\SimplePdfReportExporter;
use RuntimeException;
use Throwable;

/**
 * reports:contract-smoke CLI command.
 *
 * Responsibility: Runs the reports:contract-smoke command to verify provider-based reporting contracts, formats, and sad-path validation without database state.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ReportsContractSmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Exposes CLI parser metadata only; command behavior stays inside execute().
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'reports:contract-smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Verify provider-based reports, CSV/XLS/PDF exporters, and contract sad paths';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Coordinates the smoke scenario and returns a process exit code without hidden side effects.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $result = ['success' => false, 'steps' => []];

        try {
            $provider = $this->provider();
            $registry = new ReportProviderRegistry();
            $registry->register($provider);
            ReportingManager::getInstance()->registerProvider($provider);
            $definition = $registry->require('framework.reports.contract-smoke')->definition();
            $rows = $provider->rows(['status' => 'open']);

            $csv = (new DataGridReportExporter(ReportFormat::CSV))->export($definition, $rows);
            $xls = (new DataGridReportExporter(ReportFormat::XLS))->export($definition, $rows);
            $pdf = (new SimplePdfReportExporter())->export($definition, $rows);

            $result['steps'][] = [
                'step' => 'provider-registers-definition',
                'status' => isset($registry->definitions()['framework.reports.contract-smoke']) ? 'ok' : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'csv-export-generated',
                'status' => $csv->extension === 'csv' && str_contains($csv->contents, 'Open request') ? 'ok' : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'xls-export-generated',
                'status' => $xls->extension === 'xls' && str_contains($xls->contents, '<table') ? 'ok' : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'pdf-export-generated',
                'status' => $pdf->extension === 'pdf' && str_starts_with($pdf->contents, '%PDF-') ? 'ok' : 'failed',
            ];

            $unknownRejected = false;
            try {
                $registry->require('missing.report');
            } catch (RuntimeException) {
                $unknownRejected = true;
            }
            $result['steps'][] = [
                'step' => 'unknown-provider-rejected',
                'status' => $unknownRejected ? 'ok' : 'failed',
            ];

            $duplicateRejected = false;
            try {
                $registry->register($provider);
            } catch (RuntimeException) {
                $duplicateRejected = true;
            }
            $result['steps'][] = [
                'step' => 'duplicate-provider-rejected',
                'status' => $duplicateRejected ? 'ok' : 'failed',
            ];

            $unsupportedRejected = false;
            try {
                ReportFormat::normalize('xlsx');
            } catch (RuntimeException) {
                $unsupportedRejected = true;
            }
            $result['steps'][] = [
                'step' => 'unsupported-format-rejected',
                'status' => $unsupportedRejected ? 'ok' : 'failed',
            ];

            $badCriteriaRejected = false;
            try {
                $provider->rows(['status' => '']);
            } catch (RuntimeException) {
                $badCriteriaRejected = true;
            }
            $result['steps'][] = [
                'step' => 'bad-criteria-rejected',
                'status' => $badCriteriaRejected ? 'ok' : 'failed',
            ];

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Reports Contract Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf('  %-32s %-8s', (string) ($step['step'] ?? 'step'), strtoupper((string) ($step['status'] ?? 'unknown'))));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Reports contract smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Reports contract smoke failed.'));

        return 1;
    }

    /**
     * Creates the in-memory provider used by this smoke command.
     *
     * Responsibility: Supplies a disposable report provider fixture for contract validation.
     */
    private function provider(): ReportProviderInterface
    {
        return new class implements ReportProviderInterface {
            /**
             * Returns the provider report definition.
             *
             * Responsibility: Declares the report metadata, columns and supported formats exposed by the provider.
             */
            public function definition(): ReportDefinition
            {
                return new ReportDefinition(
                    key: 'framework.reports.contract-smoke',
                    label: 'Reports Contract Smoke',
                    filename: 'reports-contract-smoke',
                    resourceKey: 'framework.reports.contract-smoke',
                    columns: [
                        ['key' => 'id', 'label' => 'ID'],
                        ['key' => 'title', 'label' => 'Title'],
                        ['key' => 'status', 'label' => 'Status'],
                    ],
                    formats: [ReportFormat::CSV, ReportFormat::XLS, ReportFormat::PDF],
                    permissionsAny: ['reports.view'],
                    filters: [['key' => 'status', 'required' => true]]
                );
            }

            /**
             * Resolves export rows for the provided criteria.
             *
             * Responsibility: Transforms provider criteria into tabular rows without performing transport or file delivery.
             * @param array<string, mixed> $criteria
             * @return array<int, array<string, mixed>>
             */
            public function rows(array $criteria): array
            {
                $status = trim((string) ($criteria['status'] ?? ''));
                if ($status === '') {
                    throw new RuntimeException('Report status filter is required.');
                }

                return [
                    ['id' => 10, 'title' => 'Open request', 'status' => $status],
                    ['id' => 11, 'title' => 'Second request', 'status' => $status],
                ];
            }
        };
    }
}