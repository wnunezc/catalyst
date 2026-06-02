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

namespace Catalyst\Framework\Reporting;

use Catalyst\Entities\ReportRun;
use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Attachment\AttachmentManager;
use Catalyst\Framework\Attachment\AttachmentRepository;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Queue\QueueManager;
use Catalyst\Framework\Reporting\Jobs\RunReportJob;
use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;
use Throwable;

/**
 * Defines the Reporting Manager class contract.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Coordinates the reporting manager behavior within its module boundary.
 */
final class ReportingManager
{
    use SingletonTrait;

    private MediaManager $media;
    private AttachmentManager $attachments;
    private AttachmentRepository $attachmentRepository;

    /**
     * Initializes the Reporting Manager instance.
     */
    protected function __construct()
    {
        $this->media = MediaManager::getInstance();
        $this->attachments = AttachmentManager::getInstance();
        $this->attachmentRepository = AttachmentRepository::getInstance();
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, mixed>|null $attachTo
     */
    public function queue(
        string $reportKey,
        array $criteria = [],
        string $format = 'csv',
        ?array $attachTo = null
    ): ReportRun {
        $format = strtolower(trim($format)) ?: 'csv';
        if (!in_array($format, ['csv', 'xls'], true)) {
            throw new RuntimeException('Unsupported report format. Allowed formats: csv, xls.');
        }

        $run = ReportRun::create([
            'report_key' => trim($reportKey),
            'format' => $format,
            'status' => 'pending',
            'criteria_json' => $criteria,
            'attach_resource_key' => trim((string) ($attachTo['resource_key'] ?? '')) ?: null,
            'attach_record_id' => !empty($attachTo['record_id']) ? (int) $attachTo['record_id'] : null,
        ]);

        $jobId = QueueManager::getInstance()->dispatch(new RunReportJob((int) $run->getKey()));
        $run->fill(['queued_job_id' => $jobId]);
        $run->save();

        return $run;
    }

    /**
     * Processes the current workflow.
     */
    public function process(int $reportRunId): ReportRun
    {
        $run = ReportRun::find($reportRunId);
        if ($run === null) {
            throw new RuntimeException('Report run not found.');
        }

        $snapshot = $run->toArray();
        if (($snapshot['status'] ?? '') === 'completed' && (int) ($snapshot['output_media_item_id'] ?? 0) > 0) {
            return $run;
        }

        $run->fill([
            'status' => 'running',
            'started_at' => gmdate('Y-m-d H:i:s'),
            'error_message' => null,
        ]);
        $run->save();

        try {
            $definition = $this->definition((string) ($snapshot['report_key'] ?? ''));
            $criteria = is_array($snapshot['criteria_json'] ?? null) ? $snapshot['criteria_json'] : [];
            $rows = $this->rowsForDefinition((string) ($snapshot['report_key'] ?? ''), $criteria);

            $grid = DataGrid::make()
                ->columns((array) ($definition['columns'] ?? []))
                ->resourceKey((string) ($definition['resource_key'] ?? ''))
                ->exportFormats([
                    'csv' => [
                        'label' => (string) __('ui.datagrid.export_csv'),
                        'icon' => 'fa-solid fa-file-csv',
                    ],
                    'xls' => [
                        'label' => (string) __('ui.datagrid.export_xls'),
                        'icon' => 'fa-solid fa-file-excel',
                    ],
                ], (string) ($definition['filename'] ?? 'report'))->printEnabled(true, (string) __('ui.datagrid.print'));

            $format = strtolower(trim((string) ($snapshot['format'] ?? 'csv'))) ?: 'csv';
            if (!in_array($format, ['csv', 'xls'], true)) {
                throw new RuntimeException('Unsupported report format. Allowed formats: csv, xls.');
            }

            $export = $format === 'xls'
                ? $grid->exportXlsRows($rows)
                : $grid->exportCsvRows($rows);
            $mimeType = $format === 'xls' ? 'application/vnd.ms-excel' : 'text/csv';
            $media = $this->media->createGenerated(
                name: (string) ($definition['label'] ?? 'Report export') . '.' . $format,
                contents: (string) ($export['contents'] ?? ''),
                options: [
                    'mime_type' => $mimeType,
                    'extension' => $format,
                    'path_prefix' => 'generated-reports/' . trim((string) ($snapshot['report_key'] ?? 'report')),
                ]
            );

            $outputAttachmentId = null;
            $attachResourceKey = trim((string) ($snapshot['attach_resource_key'] ?? ''));
            $attachRecordId = (int) ($snapshot['attach_record_id'] ?? 0);

            if ($attachResourceKey !== '' && $attachRecordId > 0) {
                $attachment = $this->attachments->attachMedia(
                    $attachResourceKey,
                    $attachRecordId,
                    $media,
                    'report-output',
                    'report',
                    false
                );
                $outputAttachmentId = (int) $attachment->getKey();
            }

            $run->fill([
                'status' => 'completed',
                'completed_at' => gmdate('Y-m-d H:i:s'),
                'output_media_item_id' => (int) $media->getKey(),
                'output_attachment_id' => $outputAttachmentId,
                'error_message' => null,
            ]);
            $run->save();

            return $run;
        } catch (Throwable $e) {
            $run->fill([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 65535),
            ]);
            $run->save();

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    private function rowsForDefinition(string $reportKey, array $criteria): array
    {
        return match ($reportKey) {
            'framework.attachments.by-resource' => $this->attachmentRows($criteria),
            default => throw new RuntimeException('Unknown report definition: ' . $reportKey),
        };
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    private function attachmentRows(array $criteria): array
    {
        $resourceKey = trim((string) ($criteria['resource_key'] ?? ''));
        $recordId = (int) ($criteria['record_id'] ?? 0);

        if ($resourceKey === '' || $recordId <= 0) {
            throw new RuntimeException('Attachment report requires resource_key and record_id.');
        }

        return $this->attachmentRepository->reportRows($criteria);
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(string $reportKey): array
    {
        return match ($reportKey) {
            'framework.attachments.by-resource' => [
                'label' => 'Resource attachments',
                'filename' => 'resource-attachments-report',
                'resource_key' => AttachmentManager::RESOURCE_KEY,
                'columns' => [
                    ['key' => 'resource_key', 'label' => 'Resource'],
                    ['key' => 'record_id', 'label' => 'Record ID'],
                    ['key' => 'purpose', 'label' => 'Purpose'],
                    ['key' => 'attachment_type', 'label' => 'Type'],
                    ['key' => 'attachment_kind', 'label' => 'Kind'],
                    ['key' => 'asset_name', 'label' => 'Asset'],
                    ['key' => 'active', 'label' => 'Active'],
                    ['key' => 'detached_at', 'label' => 'Detached At'],
                    ['key' => 'created_at', 'label' => 'Attached At'],
                ],
            ],
            default => throw new RuntimeException('Unknown report definition: ' . $reportKey),
        };
    }
}
