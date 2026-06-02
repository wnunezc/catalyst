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

use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\MediaItem;
use Catalyst\Entities\ResourceAttachment;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;

/**
 * Defines the Attachment Manager class contract.
 *
 * @package Catalyst\Framework\Attachment
 * Responsibility: Coordinates the attachment manager behavior within its module boundary.
 */
final class AttachmentManager
{
    use SingletonTrait;

    public const RESOURCE_KEY = 'resource-attachments';

    private AttachmentRepository $repository;
    private MediaManager $media;
    private DocumentTemplateManager $documents;

    /**
     * Initializes the Attachment Manager instance.
     */
    protected function __construct()
    {
        $this->repository = AttachmentRepository::getInstance();
        $this->media = MediaManager::getInstance();
        $this->documents = DocumentTemplateManager::getInstance();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForResource(string $resourceKey, int $recordId, bool $includeDetached = false): array
    {
        return $this->repository->listForResource($resourceKey, $recordId, $includeDetached);
    }

    /**
     * Handles the attach media workflow.
     */
    public function attachMedia(
        string $resourceKey,
        int $recordId,
        MediaItem $mediaItem,
        string $purpose = 'attachment',
        string $attachmentType = 'file',
        bool $isPrimary = false
    ): ResourceAttachment {
        $this->assertResourceTarget($resourceKey, $recordId);

        return ResourceAttachment::create([
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'media_item_id' => (int) $mediaItem->getKey(),
            'purpose' => $this->normalizePurpose($purpose),
            'attachment_type' => $this->normalizeType($attachmentType),
            'is_primary' => $isPrimary ? 1 : 0,
        ]);
    }

    /**
     * Handles the attach artifact workflow.
     */
    public function attachArtifact(
        string $resourceKey,
        int $recordId,
        DocumentArtifact $artifact,
        string $purpose = 'attachment',
        string $attachmentType = 'artifact',
        bool $isPrimary = false
    ): ResourceAttachment {
        $this->assertResourceTarget($resourceKey, $recordId);

        return ResourceAttachment::create([
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'document_artifact_id' => (int) $artifact->getKey(),
            'purpose' => $this->normalizePurpose($purpose),
            'attachment_type' => $this->normalizeType($attachmentType),
            'is_primary' => $isPrimary ? 1 : 0,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function replaceMediaAttachment(
        ResourceAttachment $attachment,
        array $payload,
        ?string $purpose = null,
        ?string $attachmentType = null
    ): ResourceAttachment {
        $snapshot = $attachment->toArray();
        $mediaItemId = (int) ($snapshot['media_item_id'] ?? 0);

        if ($mediaItemId <= 0) {
            throw new RuntimeException('Attachment is not linked to a media asset.');
        }

        $mediaItem = MediaItem::find($mediaItemId);
        if ($mediaItem === null) {
            throw new RuntimeException('Linked media asset no longer exists.');
        }

        if (array_key_exists('generated_contents', $payload)) {
            $this->media->replaceGenerated(
                $mediaItem,
                (string) ($payload['generated_contents'] ?? ''),
                [
                    'name' => (string) ($payload['name'] ?? ($mediaItem->toArray()['name'] ?? 'generated-asset')),
                    'mime_type' => (string) ($payload['mime_type'] ?? ($mediaItem->toArray()['mime_type'] ?? 'text/plain')),
                    'extension' => (string) ($payload['extension'] ?? ($mediaItem->toArray()['extension'] ?? 'txt')),
                    'path_prefix' => (string) ($payload['path_prefix'] ?? 'generated-media'),
                    'disk' => (string) ($payload['disk'] ?? ($mediaItem->toArray()['disk'] ?? 'local')),
                ]
            );
        } else {
            $this->media->update($mediaItem, $payload);
        }

        if ($purpose !== null || $attachmentType !== null) {
            $attachment->fill([
                'purpose' => $purpose !== null ? $this->normalizePurpose($purpose) : ($snapshot['purpose'] ?? 'attachment'),
                'attachment_type' => $attachmentType !== null ? $this->normalizeType($attachmentType) : ($snapshot['attachment_type'] ?? 'file'),
            ]);
            $attachment->save();
        }

        return $attachment;
    }

    /**
     * Handles the detach workflow.
     */
    public function detach(ResourceAttachment $attachment, bool $deleteAsset = false): void
    {
        $snapshot = $attachment->toArray();

        if (!empty($snapshot['detached_at'])) {
            return;
        }

        $attachment->fill([
            'detached_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $attachment->save();

        if (!$deleteAsset) {
            return;
        }

        $attachmentId = (int) ($snapshot['id'] ?? 0);
        $mediaItemId = (int) ($snapshot['media_item_id'] ?? 0);
        $artifactId = (int) ($snapshot['document_artifact_id'] ?? 0);

        if ($mediaItemId > 0 && $this->repository->countActiveMediaReferences($mediaItemId, $attachmentId) === 0) {
            $mediaItem = MediaItem::find($mediaItemId);
            if ($mediaItem !== null) {
                $this->media->delete($mediaItem);
            }
        }

        if ($artifactId > 0 && $this->repository->countActiveArtifactReferences($artifactId, $attachmentId) === 0) {
            $artifact = DocumentArtifact::find($artifactId);
            if ($artifact !== null) {
                $this->documents->purgeArtifact($artifact);
            }
        }
    }

    /**
     * Handles the assert resource target workflow.
     */
    private function assertResourceTarget(string $resourceKey, int $recordId): void
    {
        if (trim($resourceKey) === '' || $recordId <= 0) {
            throw new RuntimeException('A valid resource target is required for attachments.');
        }
    }

    /**
     * Normalizes the provided value.
     */
    private function normalizePurpose(string $value): string
    {
        $value = trim($value);

        return $value === '' ? 'attachment' : mb_substr($value, 0, 80);
    }

    /**
     * Normalizes the provided value.
     */
    private function normalizeType(string $value): string
    {
        $value = trim($value);

        return $value === '' ? 'file' : mb_substr($value, 0, 80);
    }
}
