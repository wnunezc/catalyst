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
 * Declares reusable constraints for attaching files and generated artifacts.
 *
 * @package Catalyst\Framework\Attachment
 * Responsibility: Carries allowed MIME, extension, size, disk and attachment metadata constraints.
 */
final class AttachmentPolicy
{
    /**
     * Creates an immutable attachment policy.
     *
     * Responsibility: Owns attachment limits, storage visibility and evidence verification defaults as an immutable policy boundary.
     * @param string[] $allowedMimeTypes
     * @param string[] $allowedExtensions
     * @param string[] $allowedDisks
     * @param string[] $allowedPurposes
     * @param string[] $allowedAttachmentTypes
     */
    public function __construct(
        public readonly array $allowedMimeTypes = [],
        public readonly array $allowedExtensions = [],
        public readonly int $maxBytes = 0,
        public readonly array $allowedDisks = [],
        public readonly bool $requirePrivateStorage = false,
        public readonly array $allowedPurposes = [],
        public readonly array $allowedAttachmentTypes = []
    ) {
    }

    /**
     * Builds a policy for private evidence attachments.
     *
     * Responsibility: Centralizes the secure defaults used by evidence uploads so derived apps do not duplicate policy constants.
     */
    public static function privateEvidence(int $maxBytes = 10485760): self
    {
        return new self(
            allowedMimeTypes: ['application/pdf', 'image/png', 'image/jpeg', 'text/plain'],
            allowedExtensions: ['pdf', 'png', 'jpg', 'jpeg', 'txt'],
            maxBytes: $maxBytes,
            allowedDisks: ['runtime'],
            requirePrivateStorage: true,
            allowedPurposes: ['evidence', 'supporting-doc', 'verification'],
            allowedAttachmentTypes: ['file', 'artifact', 'qr-verification']
        );
    }
}