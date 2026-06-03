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
use Catalyst\Framework\Attachment\AttachmentManager;
use Catalyst\Framework\Attachment\AttachmentRepository;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Queue\QueueManager;
use Catalyst\Framework\Reporting\Jobs\RunReportJob;
use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;
use Throwable;

/**
 * Queues and generates exportable framework reports.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Persists report runs, builds report rows, stores generated exports, and optionally attaches outputs to resources.
 */
final class ReportingManager
{
    use SingletonTrait;

    private MediaManager $media;
    private AttachmentManager $attachments;
    private ReportProviderRegistry $providers;
    /**
     * @var array<string, ReportExporterInterface>
     */
    private array $exporters = [];

    /**
     * Initializes the Reporting Manager instance.
     *
     * Responsibility: Binds required collaborators or immutable state without executing the main workflow.
     */
    protected function __construct()
    {
        $this->media = MediaManager::getInstance();
        $this->attachments = AttachmentManager::getInstance();
        $this->providers = new ReportProviderRegistry();
        $this->registerDefaults();
    }

    /**
     * Registers a report provider.
     *
     * Responsibility: Updates framework registry state through an explicit, bounded mutation point.
     */
    public function registerProvider(ReportProviderInterface $provider): void
    {
        $this->providers->register($provider);
    }

    /**
     * Registers a report exporter.
     *
     * Responsibility: Updates framework registry state through an explicit, bounded mutation point.
     */
    public function registerExporter(ReportExporterInterface $exporter): void
    {
        $this->exporters[$exporter->format()] = $exporter;
    }

    /**
     * Returns registered report definitions.
     *
     * Responsibility: Provides read-only access to normalized state without mutating framework runtime.
     * @return array<string, ReportDefinition>
     */
    public function definitions(): array
    {
        return $this->providers->definitions();
    }

    /**
     * Creates a pending report run and dispatches its generation job.
     *
     * Responsibility: Coordinates the state-changing workflow after validation and returns the outcome to the caller.
     * @param array<string, mixed> $criteria
     * @param array<string, mixed>|null $attachTo
     */
    public function queue(
        string $reportKey,
        array $criteria = [],
        string $format = 'csv',
        ?array $attachTo = null
    ): ReportRun {
        $format = ReportFormat::normalize($format);
        $definition = $this->definition(trim($reportKey));

        if (!$definition->supportsFormat($format)) {
            throw new RuntimeException('Report definition does not support format: ' . $format);
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
     * Generates the requested report output and records its final state.
     *
     * Responsibility: Coordinates the state-changing workflow after validation and returns the outcome to the caller.
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
            $format = ReportFormat::normalize((string) ($snapshot['format'] ?? 'csv'));

            if (!$definition->supportsFormat($format)) {
                throw new RuntimeException('Report definition does not support format: ' . $format);
            }

            $export = $this->exporter($format)->export($definition, $rows);
            $media = $this->media->createGenerated(
                name: $export->filename,
                contents: $export->contents,
                options: [
                    'mime_type' => $export->mimeType,
                    'extension' => $export->extension,
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
     * Resolves report rows for a registered report definition.
     *
     * Responsibility: Provides a focused helper boundary used by the owning service without widening external API ownership.
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    private function rowsForDefinition(string $reportKey, array $criteria): array
    {
        return $this->providers->require($reportKey)->rows($criteria);
    }

    /**
     * Returns the registered definition for a report key.
     *
     * Responsibility: Provides read-only access to normalized state without mutating framework runtime.
     */
    private function definition(string $reportKey): ReportDefinition
    {
        return $this->providers->require($reportKey)->definition();
    }

    /**
     * Returns the exporter for a normalized format.
     *
     * Responsibility: Provides read-only access to normalized state without mutating framework runtime.
     */
    private function exporter(string $format): ReportExporterInterface
    {
        $format = ReportFormat::normalize($format);

        if (!isset($this->exporters[$format])) {
            throw new RuntimeException('Report exporter is not registered for format: ' . $format);
        }

        return $this->exporters[$format];
    }

    /**
     * Registers framework-provided report providers and exporters.
     *
     * Responsibility: Updates framework registry state through an explicit, bounded mutation point.
     */
    private function registerDefaults(): void
    {
        $this->registerProvider(new AttachmentReportProvider(AttachmentRepository::getInstance()));
        $this->registerExporter(new DataGridReportExporter(ReportFormat::CSV));
        $this->registerExporter(new DataGridReportExporter(ReportFormat::XLS));
        $this->registerExporter(new SimplePdfReportExporter());
    }
}