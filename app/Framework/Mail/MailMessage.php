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

use Catalyst\Helpers\Exceptions\MailException;
use InvalidArgumentException;

/**
 * Fluent builder for one outgoing email message.
 *
 * Collects sender, recipients, body, attachments and headers before delegating
 * delivery to the bound mail manager.
 *
 * @package Catalyst\Framework\Mail
 * Responsibility: Hold and validate per-message mail state for PHPMailer delivery.
 */
class MailMessage
{
    /**
     * @var MailManager Mail manager instance used to dispatch this message
     */
    protected MailManager $mailManager;

    /**
     * @var array|null Custom from address override (email, name)
     */
    protected ?array $from = null;

    /**
     * @var array Primary recipients
     */
    protected array $to = [];

    /**
     * @var array CC recipients
     */
    protected array $cc = [];

    /**
     * @var array BCC recipients
     */
    protected array $bcc = [];

    /**
     * @var array|null Reply-to address
     */
    protected ?array $replyTo = null;

    /**
     * @var string Message subject
     */
    protected string $subject = '';

    /**
     * @var string|null HTML body
     */
    protected ?string $htmlBody = null;

    /**
     * @var string|null Plain text body
     */
    protected ?string $textBody = null;

    /**
     * @var array Attachments (regular and inline)
     */
    protected array $attachments = [];

    /**
     * @var array Custom headers
     */
    protected array $headers = [];

    /**
     * @var bool Whether this is a bulk/marketing email (vs transactional)
     */
    protected bool $isBulk = false;

    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param MailManager $mailManager Manager that will send this message
     */
    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * Override the sender identity for this message.
     *
     * Responsibility: Override the sender identity for this message.
     * @param string      $address Sender email address
     * @param string|null $name    Sender display name
     * @return self
     * @throws MailException If the address format is invalid
     */
    public function from(string $address, ?string $name = null): self
    {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw MailException::invalidAddress($address);
        }

        $this->from = ['email' => $address, 'name' => $name];
        return $this;
    }

    /**
     * Add primary recipients to the message.
     *
     * Responsibility: Add primary recipients to the message.
     * @param array|string $address Email address or array of recipients
     * @param string|null  $name    Display name (only used when $address is a string)
     * @return self
     * @throws MailException If any address is invalid
     */
    public function to(array|string $address, ?string $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipientName) {
                if (is_numeric($email)) {
                    $this->validateEmailFormat($recipientName);
                    $this->addRecipient($this->to, $recipientName);
                } else {
                    $this->validateEmailFormat($email);
                    $this->addRecipient($this->to, $email, $recipientName);
                }
            }
        } else {
            $this->validateEmailFormat($address);
            $this->addRecipient($this->to, $address, $name);
        }

        return $this;
    }

    /**
     * Add carbon-copy recipients to the message.
     *
     * Responsibility: Add carbon-copy recipients to the message.
     * @param array|string $address Email address or array of recipients
     * @param string|null  $name    Display name
     * @return self
     * @throws MailException If any address is invalid
     */
    public function cc(array|string $address, ?string $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipientName) {
                if (is_numeric($email)) {
                    $this->addRecipient($this->cc, $recipientName);
                } else {
                    $this->addRecipient($this->cc, $email, $recipientName);
                }
            }
        } else {
            $this->addRecipient($this->cc, $address, $name);
        }

        return $this;
    }

    /**
     * Add blind-carbon-copy recipients to the message.
     *
     * Responsibility: Add blind-carbon-copy recipients to the message.
     * @param array|string $address Email address or array of recipients
     * @param string|null  $name    Display name
     * @return self
     * @throws MailException If any address is invalid
     */
    public function bcc(array|string $address, ?string $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipientName) {
                if (is_numeric($email)) {
                    $this->addRecipient($this->bcc, $recipientName);
                } else {
                    $this->addRecipient($this->bcc, $email, $recipientName);
                }
            }
        } else {
            $this->addRecipient($this->bcc, $address, $name);
        }

        return $this;
    }

    /**
     * Set the reply-to identity for the message.
     *
     * Responsibility: Stores the reply-to address and display name used by the outgoing message.
     * @param string      $address Email address
     * @param string|null $name    Display name
     * @return self
     * @throws MailException If the address format is invalid
     */
    public function replyTo(string $address, ?string $name = null): self
    {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw MailException::invalidAddress($address);
        }

        $this->replyTo = ['email' => $address, 'name' => $name];
        return $this;
    }

    /**
     * Set the message subject line.
     *
     * Responsibility: Stores the subject line used when the mail message is sent.
     * @param string $subject Subject text
     * @return self
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the HTML body directly.
     *
     * Responsibility: Stores trusted HTML body content supplied directly to the message builder.
     * @param string $html HTML content
     * @return self
     */
    public function html(string $html): self
    {
        $this->htmlBody = $html;
        return $this;
    }

    /**
     * Set the plain-text body directly.
     *
     * Responsibility: Stores plain-text body content supplied directly to the message builder.
     * @param string $text Plain text content
     * @return self
     */
    public function text(string $text): self
    {
        $this->textBody = $text;
        return $this;
    }

    /**
     * Set body content and derive text fallback when HTML is detected.
     *
     * Responsibility: Set body content and derive text fallback when HTML is detected.
     * @param string $content Message body
     * @return self
     */
    public function body(string $content): self
    {
        if (preg_match('/<[^>]+>/', $content)) {
            $this->htmlBody = $content;
            if ($this->textBody === null) {
                $this->textBody = strip_tags($content);
            }
        } else {
            $this->textBody = $content;
        }

        return $this;
    }

    /**
     * Populate the message body from a named template or explicit template path.
     *
     * Responsibility: Populate the message body from a named template or explicit template path.
     * @param string $template Template name or file path
     * @param array  $variables Variables passed to the template
     * @param bool   $isPath   True if $template is an absolute file path
     * @return self
     * @throws MailException If the template cannot be loaded or rendered
     */
    public function template(string $template, array $variables = [], bool $isPath = false): self
    {
        $processor = new MailTemplate();

        $result = $isPath
            ? $processor->renderFromPath($template, $variables)
            : $processor->render($template, $variables);

        $this->htmlBody = $result['html'] ?? null;
        $this->textBody = $result['text'] ?? null;

        return $this;
    }

    /**
     * Attach a regular file to the message.
     *
     * Responsibility: Attach a regular file to the message.
     * @param string      $path     Absolute path to the file
     * @param string|null $name     Display filename (null = basename)
     * @param string      $mimeType MIME type (empty = auto-detect)
     * @return self
     * @throws MailException If the file does not exist
     */
    public function attach(string $path, ?string $name = null, string $mimeType = ''): self
    {
        if (!file_exists($path)) {
            throw MailException::attachmentError($path, 'File not found');
        }

        $this->attachments[] = [
            'path'     => $path,
            'name'     => $name ?: basename($path),
            'type'     => $mimeType,
            'encoding' => 'base64',
            'inline'   => false,
            'cid'      => '',
        ];

        return $this;
    }

    /**
     * Attach an inline file with a content ID.
     *
     * Responsibility: Attach an inline file with a content ID.
     * @param string      $path     Absolute path to the image file
     * @param string      $cid      Content-ID for the embedded file
     * @param string|null $name     Display filename
     * @param string      $mimeType MIME type
     * @return self
     * @throws MailException If the file does not exist
     */
    public function attachInline(string $path, string $cid, ?string $name = null, string $mimeType = ''): self
    {
        if (!file_exists($path)) {
            throw MailException::attachmentError($path, 'File not found');
        }

        $this->attachments[] = [
            'path'     => $path,
            'name'     => $name ?: basename($path),
            'type'     => $mimeType,
            'encoding' => 'base64',
            'inline'   => true,
            'cid'      => $cid,
        ];

        return $this;
    }

    /**
     * Mark the message for bulk-mail headers during delivery.
     *
     * Responsibility: Mark the message for bulk-mail headers during delivery.
     * @return self
     */
    public function bulk(): self
    {
        $this->isBulk = true;
        return $this;
    }

    /**
     * Add a custom mail header to the message.
     *
     * Responsibility: Add a custom mail header to the message.
     * @param string $name  Header name
     * @param string $value Header value
     * @return self
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Validate and dispatch the message through the bound manager.
     *
     * Responsibility: Validate and dispatch the message through the bound manager.
     * @return bool True on success
     * @throws MailException If validation fails or sending fails
     */
    public function send(): bool
    {
        $this->validate();
        return $this->mailManager->send($this);
    }

    // --- Getters (used by MailManager) ---------------------------------------

    /**
     * Return the message sender override.
     *
     * Responsibility: Return the message sender override.
     */
    public function getFrom(): ?array
    {
        return $this->from;
    }

    /**
     * Return primary recipients.
     *
     * Responsibility: Return primary recipients.
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * Return carbon-copy recipients.
     *
     * Responsibility: Return carbon-copy recipients.
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * Return blind-carbon-copy recipients.
     *
     * Responsibility: Return blind-carbon-copy recipients.
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * Return the reply-to identity.
     *
     * Responsibility: Return the reply-to identity.
     */
    public function getReplyTo(): ?array
    {
        return $this->replyTo;
    }

    /**
     * Return the subject line.
     *
     * Responsibility: Return the subject line.
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Return the HTML body.
     *
     * Responsibility: Return the HTML body.
     */
    public function getHtmlBody(): ?string
    {
        return $this->htmlBody;
    }

    /**
     * Return the plain-text body.
     *
     * Responsibility: Return the plain-text body.
     */
    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    /**
     * Return regular and inline attachments.
     *
     * Responsibility: Return regular and inline attachments.
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * Return custom message headers.
     *
     * Responsibility: Return custom message headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Determine whether the message has an HTML body.
     *
     * Responsibility: Determine whether the message has an HTML body.
     */
    public function isHtml(): bool
    {
        return $this->htmlBody !== null;
    }

    /**
     * Determine whether the message should use bulk-mail headers.
     *
     * Responsibility: Determine whether the message should use bulk-mail headers.
     */
    public function isBulk(): bool
    {
        return $this->isBulk;
    }

    // --- Internal helpers ----------------------------------------------------

    /**
     * Validate required recipients, subject and body before sending.
     *
     * Responsibility: Validate required recipients, subject and body before sending.
     * @throws MailException If recipients, subject, or body are missing
     */
    protected function validate(): void
    {
        if (empty($this->to)) {
            throw MailException::configurationError('No recipients specified');
        }

        if (empty($this->subject)) {
            throw MailException::configurationError('No subject specified');
        }

        if (empty($this->htmlBody) && empty($this->textBody)) {
            throw MailException::configurationError('No message body specified');
        }
    }

    /**
     * Validate address syntax and block known non-deliverable domains.
     *
     * Responsibility: Validate address syntax and block known non-deliverable domains.
     * @param string $email Email address to validate
     * @throws InvalidArgumentException If format is invalid or domain is blocked
     */
    protected function validateEmailFormat(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: $email");
        }

        $domain         = strtolower(substr(strrchr($email, '@'), 1));
        $blockedDomains = ['localhost', 'example.com', 'example.org', 'test.com'];

        if (in_array($domain, $blockedDomains, true)) {
            throw new InvalidArgumentException("Email domain not allowed: $domain");
        }
    }

    /**
     * Append a validated recipient entry to one recipient list.
     *
     * Responsibility: Append a validated recipient entry to one recipient list.
     * @param array       &$list Recipient list (to, cc, or bcc)
     * @param string       $email Email address
     * @param string|null  $name  Display name
     * @throws MailException If address format is invalid
     */
    protected function addRecipient(array &$list, string $email, ?string $name = null): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw MailException::invalidAddress($email);
        }

        $list[] = ['email' => $email, 'name' => $name];
    }
}
