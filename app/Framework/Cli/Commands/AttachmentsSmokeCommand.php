<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Entities\DocumentTemplate;
use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\MediaItem;
use Catalyst\Entities\ResourceAttachment;
use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Attachment\AttachmentManager;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Throwable;

final class AttachmentsSmokeCommand extends AbstractCommand
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
        return 'attachments:smoke';
    }

    public function getDescription(): string
    {
        return 'Exercise the canonical PA-06 attachment contract over media, document artifacts, replace and detach flows';
    }

    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
        $probe = 'attachment-smoke-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(2));
        $recordId = random_int(10000, 99999);
        $resourceKey = 'framework.attachments.smoke';
        $attachments = AttachmentManager::getInstance();
        $documents = DocumentTemplateManager::getInstance();
        $result = ['success' => false, 'steps' => []];

        try {
            SessionManager::getInstance()->init();

            $media = \Catalyst\Framework\Media\MediaManager::getInstance()->createGenerated(
                name: $probe . '.txt',
                contents: 'first-version',
                options: [
                    'mime_type' => 'text/plain',
                    'extension' => 'txt',
                    'path_prefix' => 'smoke/attachments',
                    'disk' => 'runtime',
                ]
            );
            $mediaAttachment = $attachments->attachMedia($resourceKey, $recordId, $media, 'evidence', 'file', true);

            $template = $documents->create([
                'name' => 'Attachment Smoke ' . $probe,
                'slug' => 'attachment-smoke-' . $probe,
                'description' => 'Smoke template',
                'format' => 'html',
                'variables_schema_json' => ['probe' => 'string'],
                'sample_payload_json' => ['probe' => $probe],
                'body_template' => '<article>{{ probe }}</article>',
            ]);
            $artifact = $documents->export($template, ['probe' => $probe]);
            $artifactSnapshot = $artifact->toArray();
            $result['steps'][] = [
                'step' => 'document-artifact-is-pdf',
                'status' => ($artifactSnapshot['format'] ?? '') === 'pdf'
                    && str_ends_with((string) ($artifactSnapshot['path'] ?? ''), '.pdf')
                    ? 'ok'
                    : 'failed',
            ];
            $artifactAttachment = $attachments->attachArtifact($resourceKey, $recordId, $artifact, 'supporting-doc', 'artifact');

            $attachments->replaceMediaAttachment($mediaAttachment, [
                'name' => $probe . '-replaced',
                'generated_contents' => 'second-version',
                'mime_type' => 'text/plain',
                'extension' => 'txt',
                'path_prefix' => 'smoke/attachments',
                'disk' => 'runtime',
            ], 'evidence', 'file');

            $rows = $attachments->listForResource($resourceKey, $recordId, true);
            $result['steps'][] = [
                'step' => 'link-media-and-artifact',
                'status' => count($rows) === 2 ? 'ok' : 'failed',
            ];

            $attachments->detach($mediaAttachment, true);
            $rowsAfterDetach = $attachments->listForResource($resourceKey, $recordId, true);
            $mediaStillExists = MediaItem::find((int) $media->getKey()) !== null;
            $artifactStillLinked = ResourceAttachment::find((int) $artifactAttachment->getKey()) !== null;

            $result['steps'][] = [
                'step' => 'replace-and-detach',
                'status' => count($rowsAfterDetach) === 1 && $mediaStillExists === false && $artifactStillLinked ? 'ok' : 'failed',
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
        $this->info('Attachments Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf('  %-24s %-8s', (string) ($step['step'] ?? 'step'), strtoupper((string) ($step['status'] ?? 'unknown'))));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Attachments smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Attachments smoke failed.'));

        return 1;
    }

    private function cleanupProbe(int $tenantId, string $probe): void
    {
        $db = DatabaseManager::getInstance()->connection();

        try {
            $db->execute(
                'DELETE FROM resource_attachments WHERE tenant_id = ? AND resource_key = ?',
                [$tenantId, 'framework.attachments.smoke']
            );

            $artifactRows = $db->select(
                'SELECT id FROM document_artifacts WHERE tenant_id = ? AND name LIKE ?',
                [$tenantId, '%Attachment Smoke ' . $probe . '%']
            ) ?: [];
            foreach ($artifactRows as $artifactRow) {
                $artifact = DocumentArtifact::find((int) ($artifactRow['id'] ?? 0));
                if ($artifact !== null) {
                    $this->purgeArtifact($artifact);
                }
            }

            $mediaRows = $db->select(
                'SELECT id FROM media_library WHERE tenant_id = ? AND name LIKE ?',
                [$tenantId, $probe . '%']
            ) ?: [];
            foreach ($mediaRows as $mediaRow) {
                $media = MediaItem::find((int) ($mediaRow['id'] ?? 0));
                if ($media !== null) {
                    $this->deleteMedia($media);
                }
            }

            $db->execute(
                'DELETE FROM document_artifacts WHERE tenant_id = ? AND name LIKE ?',
                [$tenantId, '%Attachment Smoke ' . $probe . '%']
            );
            $db->execute(
                'DELETE FROM document_templates WHERE tenant_id = ? AND slug = ?',
                [$tenantId, 'attachment-smoke-' . $probe]
            );
            $db->execute(
                'DELETE FROM media_library WHERE tenant_id = ? AND name LIKE ?',
                [$tenantId, $probe . '%']
            );
        } catch (Throwable) {
            $this->warn('Attachments smoke cleanup could not remove all probe data.');
        }
    }

    private function deleteMedia(MediaItem $media): void
    {
        try {
            MediaManager::getInstance()->delete($media);
        } catch (Throwable) {
            $this->warn('Attachments smoke cleanup could not remove a media object.');
        }
    }

    private function purgeArtifact(DocumentArtifact $artifact): void
    {
        try {
            DocumentTemplateManager::getInstance()->purgeArtifact($artifact);
        } catch (Throwable) {
            $this->warn('Attachments smoke cleanup could not remove a document artifact.');
        }
    }
}
