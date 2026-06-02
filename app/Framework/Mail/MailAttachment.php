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
 * Residual mail attachment DTO kept for compatibility
 *
 * The live mail pipeline uses MailMessage::attach()/attachInline() plus the
 * internal attachment arrays consumed by MailManager. This DTO is currently not
 * hydrated or consumed by that runtime flow.
 *
 * @package Catalyst\Framework\Mail
 */
class MailAttachment
{
    /**
     * @param string      $path     Absolute path to the file
     * @param string|null $name     Display filename (null = basename of $path)
     * @param string      $mimeType MIME type (empty = auto-detect by PHPMailer)
     * @param bool        $inline   True for inline/embedded image (CID attachment)
     * @param string|null $cid      Content-ID for inline attachments (e.g. "logo@example.com")
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
