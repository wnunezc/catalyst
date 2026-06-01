<?php

declare(strict_types=1);

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
use Throwable;

final class ReportingSmokeCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'reporting:smoke';
    }

    public function getDescription(): string
    {
        return 'Exercise canonical PA-10 queue + retry + persisted output flows over the unified reporting pipeline';
    }

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
                options: ['mime_type' => 'text/plain', 'extension' => 'txt', 'path_prefix' => 'smoke/reporting']
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
                'SELECT status, output_media_item_id, output_attachment_id FROM report_runs WHERE tenant_id = ? AND id = ?',
                [$tenantId, (int) $run->getKey()]
            );

            $result['steps'][] = [
                'step' => 'retry-completes-report',
                'status' => ($second['status'] ?? '') === 'processed'
                    && ($runRow['status'] ?? '') === 'completed'
                    && (int) ($runRow['output_media_item_id'] ?? 0) > 0
                    && (int) ($runRow['output_attachment_id'] ?? 0) > 0
                    ? 'ok'
                    : 'failed',
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

    private function cleanupProbe(int $tenantId, string $probe, string $resourceKey, int $recordId): void
    {
        $db = DatabaseManager::getInstance()->connection();

        try {
            $db->execute('DELETE FROM resource_attachments WHERE tenant_id = ? AND resource_key = ?', [$tenantId, $resourceKey]);
            $db->execute(
                'DELETE FROM report_runs WHERE tenant_id = ? AND attach_resource_key = ? AND attach_record_id = ?',
                [$tenantId, $resourceKey, $recordId]
            );
            $db->execute('DELETE FROM media_library WHERE tenant_id = ? AND name LIKE ?', [$tenantId, $probe . '%']);
        } catch (Throwable) {
            // Best-effort cleanup only.
        }
    }
}
