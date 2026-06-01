<?php

declare(strict_types=1);

namespace Catalyst\Framework\Retention;

use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\MediaItem;
use Catalyst\Entities\ResourceAttachment;
use Catalyst\Framework\Attachment\AttachmentRepository;
use Catalyst\Framework\Audit\AuditLogManager;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;

final class RetentionManager
{
    use SingletonTrait;

    private AttachmentRepository $attachments;
    private MediaManager $media;
    private DocumentTemplateManager $documents;

    protected function __construct()
    {
        $this->attachments = AttachmentRepository::getInstance();
        $this->media = MediaManager::getInstance();
        $this->documents = DocumentTemplateManager::getInstance();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function policies(): array
    {
        return [
            'media-library' => [
                'resource_key' => 'media-library',
                'archive_after_days' => 30,
                'purge_after_days' => 90,
                'mode' => 'archive-then-purge',
                'notes' => 'Only orphaned media without active attachments become eligible.',
            ],
            'document-artifacts' => [
                'resource_key' => 'document-artifacts',
                'archive_after_days' => 30,
                'purge_after_days' => 90,
                'mode' => 'archive-then-purge',
                'notes' => 'Only orphaned artifacts without active attachments become eligible.',
            ],
            'resource-attachments' => [
                'resource_key' => 'resource-attachments',
                'archive_after_days' => null,
                'purge_after_days' => 30,
                'mode' => 'purge-only',
                'notes' => 'Detached attachment links are purged after their audit trail is no longer operationally hot.',
            ],
            'audit-logs' => [
                'resource_key' => 'audit-logs',
                'archive_after_days' => null,
                'purge_after_days' => 180,
                'mode' => 'purge-only',
                'notes' => 'Old audit rows are purged in-place after the retention window closes.',
            ],
        ];
    }

    /**
     * @return array{success:bool,dry_run:bool,policies:array<string,array<string,mixed>>,steps:array<int,array<string,mixed>>}
     */
    public function run(?string $resourceKey = null, bool $dryRun = false, int $limit = 100): array
    {
        $policies = $this->policies();
        $steps = [];

        foreach ($this->candidateSets($resourceKey, $limit) as $candidate) {
            $steps[] = $candidate;

            if ($dryRun) {
                continue;
            }

            $this->applyCandidate($candidate);
        }

        return [
            'success' => true,
            'dry_run' => $dryRun,
            'policies' => $resourceKey !== null && isset($policies[$resourceKey])
                ? [$resourceKey => $policies[$resourceKey]]
                : $policies,
            'steps' => $steps,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function candidateSets(?string $resourceKey, int $limit): array
    {
        $groups = [
            'media-library' => $this->mediaCandidates($limit),
            'document-artifacts' => $this->artifactCandidates($limit),
            'resource-attachments' => $this->attachmentCandidates($limit),
            'audit-logs' => $this->auditCandidates($limit),
        ];

        if ($resourceKey !== null && $resourceKey !== '') {
            return $groups[$resourceKey] ?? [];
        }

        return array_merge(
            $groups['media-library'],
            $groups['document-artifacts'],
            $groups['resource-attachments'],
            $groups['audit-logs']
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mediaCandidates(int $limit): array
    {
        $rows = DatabaseManager::getInstance()->connection()->select(
            'SELECT m.id,
                    m.name,
                    m.path,
                    m.created_at,
                    m.archived_at,
                    (
                        SELECT COUNT(*)
                        FROM resource_attachments ra
                        WHERE ra.tenant_id = m.tenant_id
                          AND ra.media_item_id = m.id
                          AND ra.detached_at IS NULL
                    ) AS active_references
             FROM media_library m
             WHERE m.tenant_id = ?
               AND (
                    (m.archived_at IS NULL AND m.created_at <= ?)
                 OR (m.archived_at IS NOT NULL AND m.archived_at <= ?)
               )
             ORDER BY m.id ASC
             LIMIT ?',
            [
                $this->currentTenantId(),
                gmdate('Y-m-d H:i:s', time() - (30 * 86400)),
                gmdate('Y-m-d H:i:s', time() - (90 * 86400)),
                max(1, $limit),
            ]
        ) ?: [];

        $steps = [];

        foreach ($rows as $row) {
            if ((int) ($row['active_references'] ?? 0) > 0) {
                continue;
            }

            $steps[] = [
                'resource_key' => 'media-library',
                'record_id' => (int) ($row['id'] ?? 0),
                'action' => empty($row['archived_at']) ? 'archive' : 'purge',
                'label' => (string) ($row['name'] ?? $row['path'] ?? 'media'),
            ];
        }

        return $steps;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function artifactCandidates(int $limit): array
    {
        $rows = DatabaseManager::getInstance()->connection()->select(
            'SELECT da.id,
                    da.name,
                    da.path,
                    da.created_at,
                    da.archived_at,
                    (
                        SELECT COUNT(*)
                        FROM resource_attachments ra
                        WHERE ra.tenant_id = da.tenant_id
                          AND ra.document_artifact_id = da.id
                          AND ra.detached_at IS NULL
                    ) AS active_references
             FROM document_artifacts da
             WHERE da.tenant_id = ?
               AND (
                    (da.archived_at IS NULL AND da.created_at <= ?)
                 OR (da.archived_at IS NOT NULL AND da.archived_at <= ?)
               )
             ORDER BY da.id ASC
             LIMIT ?',
            [
                $this->currentTenantId(),
                gmdate('Y-m-d H:i:s', time() - (30 * 86400)),
                gmdate('Y-m-d H:i:s', time() - (90 * 86400)),
                max(1, $limit),
            ]
        ) ?: [];

        $steps = [];

        foreach ($rows as $row) {
            if ((int) ($row['active_references'] ?? 0) > 0) {
                continue;
            }

            $steps[] = [
                'resource_key' => 'document-artifacts',
                'record_id' => (int) ($row['id'] ?? 0),
                'action' => empty($row['archived_at']) ? 'archive' : 'purge',
                'label' => (string) ($row['name'] ?? $row['path'] ?? 'artifact'),
            ];
        }

        return $steps;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function attachmentCandidates(int $limit): array
    {
        $rows = DatabaseManager::getInstance()->connection()->select(
            'SELECT id, resource_key, record_id, purpose
             FROM resource_attachments
             WHERE tenant_id = ?
               AND detached_at IS NOT NULL
               AND detached_at <= ?
             ORDER BY id ASC
             LIMIT ?',
            [
                $this->currentTenantId(),
                gmdate('Y-m-d H:i:s', time() - (30 * 86400)),
                max(1, $limit),
            ]
        ) ?: [];

        return array_map(static fn (array $row): array => [
            'resource_key' => 'resource-attachments',
            'record_id' => (int) ($row['id'] ?? 0),
            'action' => 'purge',
            'label' => (string) ($row['resource_key'] ?? 'attachment') . '#' . (int) ($row['record_id'] ?? 0) . ':' . (string) ($row['purpose'] ?? 'attachment'),
        ], $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function auditCandidates(int $limit): array
    {
        $rows = DatabaseManager::getInstance()->connection()->select(
            'SELECT id, resource, action
             FROM audit_logs
             WHERE tenant_id = ?
               AND occurred_at <= ?
             ORDER BY id ASC
             LIMIT ?',
            [
                $this->currentTenantId(),
                gmdate('Y-m-d H:i:s', time() - (180 * 86400)),
                max(1, $limit),
            ]
        ) ?: [];

        return array_map(static fn (array $row): array => [
            'resource_key' => 'audit-logs',
            'record_id' => (int) ($row['id'] ?? 0),
            'action' => 'purge',
            'label' => (string) ($row['resource'] ?? 'audit') . ':' . (string) ($row['action'] ?? 'event'),
        ], $rows);
    }

    /**
     * @param array<string, mixed> $candidate
     */
    private function applyCandidate(array $candidate): void
    {
        $resourceKey = (string) ($candidate['resource_key'] ?? '');
        $recordId = (int) ($candidate['record_id'] ?? 0);
        $action = (string) ($candidate['action'] ?? '');

        if ($resourceKey === 'media-library') {
            $mediaItem = MediaItem::find($recordId);
            if ($mediaItem === null) {
                return;
            }

            if ($action === 'archive') {
                $this->media->archive($mediaItem);
            } else {
                $this->media->delete($mediaItem);
            }

            $this->audit($resourceKey, $recordId, $action, (string) ($candidate['label'] ?? ''));

            return;
        }

        if ($resourceKey === 'document-artifacts') {
            $artifact = DocumentArtifact::find($recordId);
            if ($artifact === null) {
                return;
            }

            if ($action === 'archive') {
                $this->documents->archiveArtifact($artifact);
            } else {
                $this->documents->purgeArtifact($artifact);
            }

            $this->audit($resourceKey, $recordId, $action, (string) ($candidate['label'] ?? ''));

            return;
        }

        if ($resourceKey === 'resource-attachments') {
            $attachment = ResourceAttachment::find($recordId);
            if ($attachment === null) {
                return;
            }

            $attachment->delete();
            $this->audit($resourceKey, $recordId, $action, (string) ($candidate['label'] ?? ''));

            return;
        }

        if ($resourceKey === 'audit-logs') {
            DatabaseManager::getInstance()->connection()->execute(
                'DELETE FROM audit_logs WHERE tenant_id = ? AND id = ?',
                [$this->currentTenantId(), $recordId]
            );

            $this->audit('audit-logs-retention', $recordId, $action, (string) ($candidate['label'] ?? ''));
        }
    }

    private function audit(string $resourceKey, int $recordId, string $action, string $label): void
    {
        AuditLogManager::getInstance()->recordOperation(
            channel: 'retention',
            action: $action,
            resource: $resourceKey,
            resourceId: $recordId,
            resourceLabel: $label,
            metadata: [
                'policy_group' => $resourceKey,
                'source' => 'retention-manager',
            ]
        );
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
