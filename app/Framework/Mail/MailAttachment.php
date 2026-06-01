<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
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
