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

namespace Catalyst\Framework\Mail;

/**
 * Compatibility DTO for mail attachment metadata.
 *
 * Carries file, MIME and inline content-id metadata for legacy attachment
 * contracts outside the active message builder arrays.
 *
 * @package Catalyst\Framework\Mail
 * Responsibility: Preserve typed attachment metadata for compatibility callers.
 */
class MailAttachment
{
    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param string      $path     Absolute path to the file
     * @param string|null $name     Display filename
     * @param string      $mimeType MIME type
     * @param bool        $inline   Whether the attachment is inline
     * @param string|null $cid      Content-ID for inline attachments
     */
    public function __construct(
        public readonly string $path,
        public readonly ?string $name = null,
        public readonly string $mimeType = '',
        public readonly bool $inline = false,
        public readonly ?string $cid = null,
    ) {
    }
}
