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

namespace Catalyst\Framework\Attachment;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Reads tenant-scoped resource attachment data and reference counts.
 *
 * @package Catalyst\Framework\Attachment
 * Responsibility: Query attachment listings, reporting rows and active asset references for the current tenant.
 */
final class AttachmentRepository
{
    use SingletonTrait;

    /**
     * Returns attachment rows for a resource record with joined media and artifact metadata.
     *
     * Responsibility: Returns attachment rows for a resource record with joined media and artifact metadata.
     * @return array<int, array<string, mixed>>
     */
    public function listForResource(string $resourceKey, int $recordId, bool $includeDetached = false): array
    {
        $whereDetached = $includeDetached ? '' : ' AND ra.detached_at IS NULL';

        return DatabaseManager::getInstance()->connection()->select(
            'SELECT ra.*,
                    mi.name AS media_name,
                    mi.original_name AS media_original_name,
                    mi.public_url AS media_public_url,
                    mi.archived_at AS media_archived_at,
                    da.name AS artifact_name,
                    da.public_url AS artifact_public_url,
                    da.archived_at AS artifact_archived_at
             FROM resource_attachments ra
             LEFT JOIN media_library mi
                ON mi.id = ra.media_item_id
               AND mi.tenant_id = ra.tenant_id
             LEFT JOIN document_artifacts da
                ON da.id = ra.document_artifact_id
               AND da.tenant_id = ra.tenant_id
             WHERE ra.tenant_id = ?
               AND ra.resource_key = ?
               AND ra.record_id = ?'
             . $whereDetached .
             ' ORDER BY ra.id ASC',
            [$this->currentTenantId(), $resourceKey, $recordId]
        ) ?: [];
    }

    /**
     * Builds normalized report rows for a resource attachment listing.
     *
     * Responsibility: Builds normalized report rows for a resource attachment listing.
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    public function reportRows(array $criteria): array
    {
        $resourceKey = trim((string) ($criteria['resource_key'] ?? ''));
        $recordId = (int) ($criteria['record_id'] ?? 0);
        $includeDetached = (bool) ($criteria['include_detached'] ?? false);

        $rows = $this->listForResource($resourceKey, $recordId, $includeDetached);

        return array_map(static function (array $row): array {
            $kind = (int) ($row['media_item_id'] ?? 0) > 0 ? 'media' : 'document-artifact';
            $assetName = $kind === 'media'
                ? (string) ($row['media_name'] ?? $row['media_original_name'] ?? '')
                : (string) ($row['artifact_name'] ?? '');

            return [
                'id' => (int) ($row['id'] ?? 0),
                'resource_key' => (string) ($row['resource_key'] ?? ''),
                'record_id' => (int) ($row['record_id'] ?? 0),
                'purpose' => (string) ($row['purpose'] ?? ''),
                'attachment_type' => (string) ($row['attachment_type'] ?? ''),
                'attachment_kind' => $kind,
                'asset_name' => $assetName,
                'asset_public_url' => $kind === 'media'
                    ? (string) ($row['media_public_url'] ?? '')
                    : (string) ($row['artifact_public_url'] ?? ''),
                'active' => empty($row['detached_at']),
                'detached_at' => $row['detached_at'] ?? null,
                'created_at' => $row['created_at'] ?? null,
            ];
        }, $rows);
    }

    /**
     * Counts active attachment rows that still reference a media item.
     *
     * Responsibility: Counts active attachment rows that still reference a media item.
     */
    public function countActiveMediaReferences(int $mediaItemId, ?int $excludeAttachmentId = null): int
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM resource_attachments
                WHERE tenant_id = ?
                  AND media_item_id = ?
                  AND detached_at IS NULL';
        $bindings = [$this->currentTenantId(), $mediaItemId];

        if ($excludeAttachmentId !== null) {
            $sql .= ' AND id <> ?';
            $bindings[] = $excludeAttachmentId;
        }

        $row = DatabaseManager::getInstance()->connection()->selectOne($sql, $bindings);

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Counts active attachment rows that still reference a document artifact.
     *
     * Responsibility: Counts active attachment rows that still reference a document artifact.
     */
    public function countActiveArtifactReferences(int $artifactId, ?int $excludeAttachmentId = null): int
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM resource_attachments
                WHERE tenant_id = ?
                  AND document_artifact_id = ?
                  AND detached_at IS NULL';
        $bindings = [$this->currentTenantId(), $artifactId];

        if ($excludeAttachmentId !== null) {
            $sql .= ' AND id <> ?';
            $bindings[] = $excludeAttachmentId;
        }

        $row = DatabaseManager::getInstance()->connection()->selectOne($sql, $bindings);

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Resolves the required tenant identifier for attachment queries.
     *
     * Responsibility: Resolves the required tenant identifier for attachment queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
