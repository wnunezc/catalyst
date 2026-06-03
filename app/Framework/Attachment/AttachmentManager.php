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
 * Coordinates resource attachments for media assets and document artifacts.
 *
 * @package Catalyst\Framework\Attachment
 * Responsibility: Attach, replace, list and detach resource-owned assets while preserving reference safety.
 */
final class AttachmentManager
{
    use SingletonTrait;

    public const RESOURCE_KEY = 'resource-attachments';

    private AttachmentRepository $repository;
    private MediaManager $media;
    private DocumentTemplateManager $documents;
    private AttachmentPolicyValidator $policyValidator;

    /**
     * Initializes the Attachment Manager instance.
     *
     * Responsibility: Binds required collaborators or immutable state without executing the main workflow.
     */
    protected function __construct()
    {
        $this->repository = AttachmentRepository::getInstance();
        $this->media = MediaManager::getInstance();
        $this->documents = DocumentTemplateManager::getInstance();
        $this->policyValidator = new AttachmentPolicyValidator();
    }

    /**
     * Lists attachments for a resource record, optionally including detached rows.
     *
     * Responsibility: Defines the focused behavior owned by this method and keeps side effects limited to its caller contract.
     * @return array<int, array<string, mixed>>
     */
    public function listForResource(string $resourceKey, int $recordId, bool $includeDetached = false): array
    {
        return $this->repository->listForResource($resourceKey, $recordId, $includeDetached);
    }

    /**
     * Creates an attachment row that links a media library item to a resource record.
     *
     * Responsibility: Coordinates the state-changing workflow after validation and returns the outcome to the caller.
     */
    public function attachMedia(
        string $resourceKey,
        int $recordId,
        MediaItem $mediaItem,
        string $purpose = 'attachment',
        string $attachmentType = 'file',
        bool $isPrimary = false,
        ?AttachmentPolicy $policy = null
    ): ResourceAttachment {
        $this->assertResourceTarget($resourceKey, $recordId);
        $purpose = $this->normalizePurpose($purpose);
        $attachmentType = $this->normalizeType($attachmentType);
        $this->assertMediaPolicy($mediaItem, $policy, $purpose, $attachmentType);

        return ResourceAttachment::create([
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'media_item_id' => (int) $mediaItem->getKey(),
            'purpose' => $purpose,
            'attachment_type' => $attachmentType,
            'is_primary' => $isPrimary ? 1 : 0,
        ]);
    }

    /**
     * Creates an attachment row that links a document artifact to a resource record.
     *
     * Responsibility: Coordinates the state-changing workflow after validation and returns the outcome to the caller.
     */
    public function attachArtifact(
        string $resourceKey,
        int $recordId,
        DocumentArtifact $artifact,
        string $purpose = 'attachment',
        string $attachmentType = 'artifact',
        bool $isPrimary = false,
        ?AttachmentPolicy $policy = null
    ): ResourceAttachment {
        $this->assertResourceTarget($resourceKey, $recordId);
        $purpose = $this->normalizePurpose($purpose);
        $attachmentType = $this->normalizeType($attachmentType);
        $this->assertArtifactPolicy($artifact, $policy, $purpose, $attachmentType);

        return ResourceAttachment::create([
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'document_artifact_id' => (int) $artifact->getKey(),
            'purpose' => $purpose,
            'attachment_type' => $attachmentType,
            'is_primary' => $isPrimary ? 1 : 0,
        ]);
    }

    /**
     * Updates the media asset behind an attachment and optionally changes attachment metadata.
     *
     * Responsibility: Coordinates the state-changing workflow after validation and returns the outcome to the caller.
     * @param array<string, mixed> $payload
     */
    public function replaceMediaAttachment(
        ResourceAttachment $attachment,
        array $payload,
        ?string $purpose = null,
        ?string $attachmentType = null,
        ?AttachmentPolicy $policy = null
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

        $nextPurpose = $purpose !== null ? $this->normalizePurpose($purpose) : (string) ($snapshot['purpose'] ?? 'attachment');
        $nextAttachmentType = $attachmentType !== null ? $this->normalizeType($attachmentType) : (string) ($snapshot['attachment_type'] ?? 'file');
        $this->assertMediaPayloadPolicy($mediaItem, $payload, $policy, $nextPurpose, $nextAttachmentType);

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

        $updatedMediaItem = MediaItem::find($mediaItemId);
        if ($updatedMediaItem instanceof MediaItem) {
            $this->assertMediaPolicy(
                $updatedMediaItem,
                $policy,
                $nextPurpose,
                $nextAttachmentType
            );
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
     * Marks an attachment as detached and optionally deletes unreferenced linked assets.
     *
     * Responsibility: Coordinates the state-changing workflow after validation and returns the outcome to the caller.
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
     * Rejects empty resource keys and invalid record identifiers before attachment writes.
     *
     * Responsibility: Enforces framework invariants before data crosses into persistence, execution or rendering boundaries.
     */
    private function assertResourceTarget(string $resourceKey, int $recordId): void
    {
        if (trim($resourceKey) === '' || $recordId <= 0) {
            throw new RuntimeException('A valid resource target is required for attachments.');
        }
    }

    /**
     * Applies a policy to a media attachment before linking it.
     *
     * Responsibility: Enforces framework invariants before data crosses into persistence, execution or rendering boundaries.
     */
    private function assertMediaPolicy(MediaItem $mediaItem, ?AttachmentPolicy $policy, string $purpose, string $attachmentType): void
    {
        if ($policy === null) {
            return;
        }

        $errors = $this->policyValidator->validateMedia($mediaItem->toArray(), $policy, $purpose, $attachmentType);
        if ($errors !== []) {
            throw new RuntimeException('Attachment policy rejected media: ' . implode(' ', $errors));
        }
    }

    /**
     * Applies a policy to replacement media metadata before storage mutation when possible.
     *
     * Responsibility: Enforces framework invariants before data crosses into persistence, execution or rendering boundaries.
     * @param array<string, mixed> $payload
     */
    private function assertMediaPayloadPolicy(
        MediaItem $mediaItem,
        array $payload,
        ?AttachmentPolicy $policy,
        string $purpose,
        string $attachmentType
    ): void {
        if ($policy === null) {
            return;
        }

        $snapshot = $mediaItem->toArray();
        $file = $payload['asset_file'] ?? null;
        if ($file instanceof \Catalyst\Framework\Http\UploadedFile) {
            $snapshot['mime_type'] = $file->getMimeType();
            $snapshot['extension'] = $file->getExtension();
            $snapshot['size_bytes'] = $file->getSize();
            $snapshot['disk'] = trim((string) ($payload['disk'] ?? ($snapshot['disk'] ?? 'local'))) ?: 'local';
            $snapshot['public_url'] = $snapshot['disk'] === 'runtime' ? '' : '/media-library/pending';
        } elseif (array_key_exists('generated_contents', $payload)) {
            $snapshot['mime_type'] = (string) ($payload['mime_type'] ?? ($snapshot['mime_type'] ?? 'text/plain'));
            $snapshot['extension'] = (string) ($payload['extension'] ?? ($snapshot['extension'] ?? 'txt'));
            $snapshot['size_bytes'] = strlen((string) ($payload['generated_contents'] ?? ''));
            $snapshot['disk'] = trim((string) ($payload['disk'] ?? ($snapshot['disk'] ?? 'local'))) ?: 'local';
            $snapshot['public_url'] = $snapshot['disk'] === 'runtime' ? '' : '/generated-media/pending';
        }

        $errors = $this->policyValidator->validateMedia($snapshot, $policy, $purpose, $attachmentType);
        if ($errors !== []) {
            throw new RuntimeException('Attachment policy rejected media: ' . implode(' ', $errors));
        }
    }

    /**
     * Applies a policy to a document artifact before linking it.
     *
     * Responsibility: Enforces framework invariants before data crosses into persistence, execution or rendering boundaries.
     */
    private function assertArtifactPolicy(DocumentArtifact $artifact, ?AttachmentPolicy $policy, string $purpose, string $attachmentType): void
    {
        if ($policy === null) {
            return;
        }

        $errors = $this->policyValidator->validateArtifact($artifact->toArray(), $policy, $purpose, $attachmentType);
        if ($errors !== []) {
            throw new RuntimeException('Attachment policy rejected artifact: ' . implode(' ', $errors));
        }
    }

    /**
     * Trims and bounds the attachment purpose with a default fallback.
     *
     * Responsibility: Converts caller or catalog input into the canonical shape required by downstream services.
     */
    private function normalizePurpose(string $value): string
    {
        $value = trim($value);

        return $value === '' ? 'attachment' : mb_substr($value, 0, 80);
    }

    /**
     * Trims and bounds the attachment type with a default fallback.
     *
     * Responsibility: Converts caller or catalog input into the canonical shape required by downstream services.
     */
    private function normalizeType(string $value): string
    {
        $value = trim($value);

        return $value === '' ? 'file' : mb_substr($value, 0, 80);
    }
}