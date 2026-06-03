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

/**
 * Validates attachment metadata against a reusable policy.
 *
 * @package Catalyst\Framework\Attachment
 * Responsibility: Evaluates attachment snapshots before they are linked to resources.
 */
final class AttachmentPolicyValidator
{
    /**
     * Returns policy violations for a media item snapshot.
     *
     * Responsibility: Evaluates media metadata against extension, MIME and size limits without persisting or mutating the attachment.
     * @param array<string, mixed> $media
     * @return array<int, string>
     */
    public function validateMedia(array $media, AttachmentPolicy $policy, string $purpose, string $attachmentType): array
    {
        return $this->validate([
            'mime_type' => (string) ($media['mime_type'] ?? ''),
            'extension' => (string) ($media['extension'] ?? ''),
            'size_bytes' => (int) ($media['size_bytes'] ?? 0),
            'disk' => (string) ($media['disk'] ?? ''),
            'public_url' => (string) ($media['public_url'] ?? ''),
        ], $policy, $purpose, $attachmentType);
    }

    /**
     * Returns policy violations for a document artifact snapshot.
     *
     * Responsibility: Evaluates generated artifact metadata against policy limits before it enters framework storage.
     * @param array<string, mixed> $artifact
     * @return array<int, string>
     */
    public function validateArtifact(array $artifact, AttachmentPolicy $policy, string $purpose, string $attachmentType): array
    {
        $format = strtolower((string) ($artifact['format'] ?? ''));
        $path = strtolower((string) ($artifact['path'] ?? ''));
        $extension = $format !== '' ? $format : pathinfo($path, PATHINFO_EXTENSION);

        return $this->validate([
            'mime_type' => $extension === 'pdf' ? 'application/pdf' : '',
            'extension' => $extension,
            'size_bytes' => (int) ($artifact['size_bytes'] ?? 0),
            'disk' => (string) ($artifact['disk'] ?? ''),
            'public_url' => (string) ($artifact['public_url'] ?? ''),
        ], $policy, $purpose, $attachmentType);
    }

    /**
     * Determines whether the supplied snapshot satisfies the attachment policy.
     *
     * Responsibility: Coordinates attachment policy checks and reports every violation needed by validators and smoke tests.
     * @param array<string, mixed> $snapshot
     * @return array<int, string>
     */
    private function validate(array $snapshot, AttachmentPolicy $policy, string $purpose, string $attachmentType): array
    {
        $errors = [];
        $mimeType = strtolower(trim((string) ($snapshot['mime_type'] ?? '')));
        $extension = strtolower(ltrim(trim((string) ($snapshot['extension'] ?? '')), '.'));
        $disk = trim((string) ($snapshot['disk'] ?? ''));
        $publicUrl = trim((string) ($snapshot['public_url'] ?? ''));

        if ($policy->allowedMimeTypes !== [] && !in_array($mimeType, array_map('strtolower', $policy->allowedMimeTypes), true)) {
            $errors[] = sprintf('MIME type "%s" is not allowed.', $mimeType);
        }

        if ($policy->allowedExtensions !== [] && !in_array($extension, array_map('strtolower', $policy->allowedExtensions), true)) {
            $errors[] = sprintf('Extension "%s" is not allowed.', $extension);
        }

        $sizeBytes = (int) ($snapshot['size_bytes'] ?? 0);
        if ($policy->maxBytes > 0 && $sizeBytes > $policy->maxBytes) {
            $errors[] = sprintf('Attachment size %d exceeds limit %d.', $sizeBytes, $policy->maxBytes);
        }

        if ($policy->allowedDisks !== [] && !in_array($disk, $policy->allowedDisks, true)) {
            $errors[] = sprintf('Storage disk "%s" is not allowed.', $disk);
        }

        if ($policy->requirePrivateStorage && ($disk !== 'runtime' || $publicUrl !== '')) {
            $errors[] = 'Private storage is required.';
        }

        if ($policy->allowedPurposes !== [] && !in_array($purpose, $policy->allowedPurposes, true)) {
            $errors[] = sprintf('Attachment purpose "%s" is not allowed.', $purpose);
        }

        if ($policy->allowedAttachmentTypes !== [] && !in_array($attachmentType, $policy->allowedAttachmentTypes, true)) {
            $errors[] = sprintf('Attachment type "%s" is not allowed.', $attachmentType);
        }

        return $errors;
    }
}