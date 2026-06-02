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
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Retention\RetentionManager;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\MediaItem;
use Throwable;

/**
 * Defines the Retention Smoke Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the retention smoke command behavior within its module boundary.
 */
final class RetentionSmokeCommand extends AbstractCommand
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
        return 'retention:smoke';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical PA-05 dry-run, archive and purge flows over media, artifacts, attachments and audit rows';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $probe = 'retention-smoke-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(2));
        $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
        $result = ['success' => false, 'steps' => []];

        try {
            SessionManager::getInstance()->init();

            $media = MediaManager::getInstance()->createGenerated(
                name: $probe . '.txt',
                contents: 'retention-media',
                options: ['mime_type' => 'text/plain', 'extension' => 'txt', 'path_prefix' => 'smoke/retention', 'disk' => 'runtime']
            );
            $template = DocumentTemplateManager::getInstance()->create([
                'name' => 'Retention Smoke ' . $probe,
                'slug' => 'retention-smoke-' . $probe,
                'description' => 'Retention smoke template',
                'format' => 'html',
                'variables_schema_json' => ['probe' => 'string'],
                'sample_payload_json' => ['probe' => $probe],
                'body_template' => '<article>{{ probe }}</article>',
            ]);
            $artifact = DocumentTemplateManager::getInstance()->export($template, ['probe' => $probe]);
            $attachment = AttachmentManager::getInstance()->attachMedia('framework.retention.smoke', random_int(10000, 99999), $media, 'retention', 'file');
            AttachmentManager::getInstance()->detach($attachment, false);

            $db = DatabaseManager::getInstance()->connection();
            $db->execute('UPDATE media_library SET created_at = ?, updated_at = ? WHERE tenant_id = ? AND id = ?', [
                gmdate('Y-m-d H:i:s', time() - (40 * 86400)),
                gmdate('Y-m-d H:i:s', time() - (40 * 86400)),
                $tenantId,
                (int) $media->getKey(),
            ]);
            $db->execute('UPDATE document_artifacts SET created_at = ?, updated_at = ? WHERE tenant_id = ? AND id = ?', [
                gmdate('Y-m-d H:i:s', time() - (40 * 86400)),
                gmdate('Y-m-d H:i:s', time() - (40 * 86400)),
                $tenantId,
                (int) $artifact->getKey(),
            ]);
            $db->execute('UPDATE resource_attachments SET detached_at = ?, updated_at = ? WHERE tenant_id = ? AND id = ?', [
                gmdate('Y-m-d H:i:s', time() - (40 * 86400)),
                gmdate('Y-m-d H:i:s', time() - (40 * 86400)),
                $tenantId,
                (int) $attachment->getKey(),
            ]);
            $db->execute(
                'INSERT INTO audit_logs (tenant_id, channel, action, resource, resource_label, actor_type, metadata, occurred_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $tenantId,
                    'smoke',
                    'probe',
                    'retention-smoke',
                    $probe,
                    'system-cli',
                    json_encode(['probe' => $probe], JSON_THROW_ON_ERROR),
                    gmdate('Y-m-d H:i:s', time() - (190 * 86400)),
                ]
            );
            $auditRow = $db->selectOne(
                'SELECT id FROM audit_logs WHERE tenant_id = ? AND resource = ? AND resource_label = ? ORDER BY id DESC LIMIT 1',
                [$tenantId, 'retention-smoke', $probe]
            );
            $scope = [
                'media-library' => [(int) $media->getKey()],
                'document-artifacts' => [(int) $artifact->getKey()],
                'resource-attachments' => [(int) $attachment->getKey()],
                'audit-logs' => [(int) ($auditRow['id'] ?? 0)],
            ];

            $dryRun = RetentionManager::getInstance()->run(null, true, 10000, $scope);
            $actions = array_map(
                static fn (array $step): string => (string) ($step['resource_key'] ?? '') . ':' . (string) ($step['action'] ?? ''),
                (array) ($dryRun['steps'] ?? [])
            );
            $result['steps'][] = [
                'step' => 'dry-run-detects-candidates',
                'status' => in_array('media-library:archive', $actions, true)
                    && in_array('document-artifacts:archive', $actions, true)
                    && in_array('resource-attachments:purge', $actions, true)
                    && in_array('audit-logs:purge', $actions, true)
                    ? 'ok'
                    : 'failed',
            ];

            RetentionManager::getInstance()->run(null, false, 10000, $scope);

            $db->execute('UPDATE media_library SET archived_at = ? WHERE tenant_id = ? AND id = ?', [
                gmdate('Y-m-d H:i:s', time() - (95 * 86400)),
                $tenantId,
                (int) $media->getKey(),
            ]);
            $db->execute('UPDATE document_artifacts SET archived_at = ? WHERE tenant_id = ? AND id = ?', [
                gmdate('Y-m-d H:i:s', time() - (95 * 86400)),
                $tenantId,
                (int) $artifact->getKey(),
            ]);
            RetentionManager::getInstance()->run(null, false, 10000, $scope);

            $remainingMedia = $db->selectOne('SELECT id, archived_at FROM media_library WHERE tenant_id = ? AND id = ?', [$tenantId, (int) $media->getKey()]);
            $remainingArtifact = $db->selectOne('SELECT id, archived_at FROM document_artifacts WHERE tenant_id = ? AND id = ?', [$tenantId, (int) $artifact->getKey()]);
            $remainingAttachment = $db->selectOne('SELECT id FROM resource_attachments WHERE tenant_id = ? AND id = ?', [$tenantId, (int) $attachment->getKey()]);
            $remainingAudit = $db->selectOne('SELECT id FROM audit_logs WHERE tenant_id = ? AND resource = ? AND resource_label = ?', [$tenantId, 'retention-smoke', $probe]);

            $result['steps'][] = [
                'step' => 'archive-then-purge',
                'status' => $remainingMedia === null && $remainingArtifact === null && $remainingAttachment === null && $remainingAudit === null
                    ? 'ok'
                    : 'failed',
            ];

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            $this->cleanupProbe($tenantId, $probe);
            SessionManager::getInstance()->destroy();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Retention Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf('  %-24s %-8s', (string) ($step['step'] ?? 'step'), strtoupper((string) ($step['status'] ?? 'unknown'))));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Retention smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Retention smoke failed.'));

        return 1;
    }

    /**
     * Handles the cleanup probe workflow.
     */
    private function cleanupProbe(int $tenantId, string $probe): void
    {
        $db = DatabaseManager::getInstance()->connection();

        try {
            $db->execute('DELETE FROM resource_attachments WHERE tenant_id = ? AND resource_key = ?', [$tenantId, 'framework.retention.smoke']);

            $artifactRows = $db->select(
                'SELECT id FROM document_artifacts WHERE tenant_id = ? AND name LIKE ?',
                [$tenantId, '%Retention Smoke ' . $probe . '%']
            ) ?: [];
            foreach ($artifactRows as $artifactRow) {
                $artifact = DocumentArtifact::find((int) ($artifactRow['id'] ?? 0));
                if ($artifact !== null) {
                    DocumentTemplateManager::getInstance()->purgeArtifact($artifact);
                }
            }

            $mediaRows = $db->select(
                'SELECT id FROM media_library WHERE tenant_id = ? AND name LIKE ?',
                [$tenantId, $probe . '%']
            ) ?: [];
            foreach ($mediaRows as $mediaRow) {
                $media = MediaItem::find((int) ($mediaRow['id'] ?? 0));
                if ($media !== null) {
                    MediaManager::getInstance()->delete($media);
                }
            }

            $db->execute('DELETE FROM document_artifacts WHERE tenant_id = ? AND name LIKE ?', [$tenantId, '%Retention Smoke ' . $probe . '%']);
            $db->execute('DELETE FROM document_templates WHERE tenant_id = ? AND slug = ?', [$tenantId, 'retention-smoke-' . $probe]);
            $db->execute('DELETE FROM media_library WHERE tenant_id = ? AND name LIKE ?', [$tenantId, $probe . '%']);
            $db->execute('DELETE FROM audit_logs WHERE tenant_id = ? AND resource = ? AND resource_label = ?', [$tenantId, 'retention-smoke', $probe]);
        } catch (Throwable) {
            $this->warn('Retention smoke cleanup could not remove all probe data.');
        }
    }
}
