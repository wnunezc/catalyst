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
use Catalyst\Framework\Attachment\AttachmentManager;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Queue\QueueRepository;
use Catalyst\Framework\Queue\QueueWorker;
use Catalyst\Framework\Reporting\ReportingManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Entities\MediaItem;
use Throwable;

/**
 * Defines the Reporting Smoke Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the reporting smoke command behavior within its module boundary.
 */
final class ReportingSmokeCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'reporting:smoke';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical PA-10 queue + retry + persisted output flows over the unified reporting pipeline';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
        $probe = 'reporting-smoke-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(2));
        $recordId = random_int(10000, 99999);
        $resourceKey = 'framework.reporting.smoke';
        $worker = new QueueWorker();
        $result = ['success' => false, 'steps' => []];

        try {
            $media = MediaManager::getInstance()->createGenerated(
                name: $probe . '.txt',
                contents: 'reporting-smoke',
                options: ['mime_type' => 'text/plain', 'extension' => 'txt', 'path_prefix' => 'smoke/reporting', 'disk' => 'runtime']
            );
            AttachmentManager::getInstance()->attachMedia($resourceKey, $recordId, $media, 'source', 'file');

            $run = ReportingManager::getInstance()->queue(
                'framework.attachments.by-resource',
                ['resource_key' => $resourceKey],
                'csv',
                ['resource_key' => $resourceKey, 'record_id' => $recordId]
            );

            $first = $worker->processNext('reports');
            $failedRows = QueueRepository::getInstance()->listFailed(10, 'reports');
            $failedId = (int) ($failedRows[0]['id'] ?? 0);

            $result['steps'][] = [
                'step' => 'first-run-fails-and-persists',
                'status' => ($first['status'] ?? '') === 'failed' && $failedId > 0 ? 'ok' : 'failed',
            ];

            DatabaseManager::getInstance()->connection()->execute(
                'UPDATE report_runs SET criteria_json = ? WHERE tenant_id = ? AND id = ?',
                [
                    json_encode(['resource_key' => $resourceKey, 'record_id' => $recordId], JSON_THROW_ON_ERROR),
                    $tenantId,
                    (int) $run->getKey(),
                ]
            );

            QueueRepository::getInstance()->retryFailed($failedId);
            $second = $worker->processNext('reports');
            $runRow = DatabaseManager::getInstance()->connection()->selectOne(
                'SELECT rr.status, rr.output_media_item_id, rr.output_attachment_id, m.extension, m.mime_type
                 FROM report_runs rr
                 LEFT JOIN media_library m
                    ON m.tenant_id = rr.tenant_id
                   AND m.id = rr.output_media_item_id
                 WHERE rr.tenant_id = ? AND rr.id = ?',
                [$tenantId, (int) $run->getKey()]
            );

            $result['steps'][] = [
                'step' => 'retry-completes-report',
                'status' => ($second['status'] ?? '') === 'processed'
                    && ($runRow['status'] ?? '') === 'completed'
                    && (int) ($runRow['output_media_item_id'] ?? 0) > 0
                    && (int) ($runRow['output_attachment_id'] ?? 0) > 0
                    && ($runRow['extension'] ?? '') === 'csv'
                    && ($runRow['mime_type'] ?? '') === 'text/csv'
                    ? 'ok'
                    : 'failed',
            ];

            $xlsRun = ReportingManager::getInstance()->queue(
                'framework.attachments.by-resource',
                ['resource_key' => $resourceKey, 'record_id' => $recordId],
                'xls',
                ['resource_key' => $resourceKey, 'record_id' => $recordId]
            );
            $third = $worker->processNext('reports');
            $xlsRow = DatabaseManager::getInstance()->connection()->selectOne(
                'SELECT rr.status, m.extension, m.mime_type
                 FROM report_runs rr
                 INNER JOIN media_library m
                    ON m.tenant_id = rr.tenant_id
                   AND m.id = rr.output_media_item_id
                 WHERE rr.tenant_id = ? AND rr.id = ?',
                [$tenantId, (int) $xlsRun->getKey()]
            );

            $result['steps'][] = [
                'step' => 'xls-output-matches-request',
                'status' => ($third['status'] ?? '') === 'processed'
                    && ($xlsRow['status'] ?? '') === 'completed'
                    && ($xlsRow['extension'] ?? '') === 'xls'
                    && ($xlsRow['mime_type'] ?? '') === 'application/vnd.ms-excel'
                    ? 'ok'
                    : 'failed',
            ];

            $unsupportedRejected = false;
            try {
                ReportingManager::getInstance()->queue(
                    'framework.attachments.by-resource',
                    ['resource_key' => $resourceKey, 'record_id' => $recordId],
                    'html'
                );
            } catch (\RuntimeException) {
                $unsupportedRejected = true;
            }
            $result['steps'][] = [
                'step' => 'unsupported-format-rejected',
                'status' => $unsupportedRejected ? 'ok' : 'failed',
            ];

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            $this->cleanupProbe($tenantId, $probe, $resourceKey, $recordId);
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Reporting Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf('  %-24s %-8s', (string) ($step['step'] ?? 'step'), strtoupper((string) ($step['status'] ?? 'unknown'))));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Reporting smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Reporting smoke failed.'));

        return 1;
    }

    /**
     * Handles the cleanup probe workflow.
     */
    private function cleanupProbe(int $tenantId, string $probe, string $resourceKey, int $recordId): void
    {
        $db = DatabaseManager::getInstance()->connection();

        try {
            $mediaRows = $db->select(
                'SELECT DISTINCT m.id
                 FROM media_library m
                 LEFT JOIN report_runs rr
                    ON rr.tenant_id = m.tenant_id
                   AND rr.output_media_item_id = m.id
                 WHERE m.tenant_id = ?
                   AND (m.name LIKE ? OR (rr.attach_resource_key = ? AND rr.attach_record_id = ?))',
                [$tenantId, $probe . '%', $resourceKey, $recordId]
            ) ?: [];
            $db->execute('DELETE FROM resource_attachments WHERE tenant_id = ? AND resource_key = ?', [$tenantId, $resourceKey]);
            $db->execute(
                'DELETE FROM report_runs WHERE tenant_id = ? AND attach_resource_key = ? AND attach_record_id = ?',
                [$tenantId, $resourceKey, $recordId]
            );

            foreach ($mediaRows as $mediaRow) {
                $media = MediaItem::find((int) ($mediaRow['id'] ?? 0));
                if ($media !== null) {
                    MediaManager::getInstance()->delete($media);
                }
            }
        } catch (Throwable) {
            $this->warn('Reporting smoke cleanup could not remove all probe data.');
        }
    }
}
