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
 * Email message with a fluent builder interface
 *
 * Represents a single outgoing email. Configure it with chained calls and
 * dispatch via send() or pass it directly to MailManager::send().
 *
 * @package Catalyst\Framework\Mail
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
     * @param MailManager $mailManager Manager that will send this message
     */
    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * Override the sender address for this message
     *
     * If not called the MailManager's configured from_address/from_name are used.
     *
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
     * Set primary recipient(s)
     *
     * Accepts a single address string, or an array in two forms:
     *   - Indexed: ['user@example.com', ...]
     *   - Associative: ['user@example.com' => 'Display Name', ...]
     *
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
     * Set CC recipient(s)
     *
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
     * Set BCC recipient(s)
     *
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
     * Set the reply-to address
     *
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
     * Set the message subject
     *
     * @param string $subject Subject text
     * @return self
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the HTML body directly
     *
     * @param string $html HTML content
     * @return self
     */
    public function html(string $html): self
    {
        $this->htmlBody = $html;
        return $this;
    }

    /**
     * Set the plain text body directly
     *
     * @param string $text Plain text content
     * @return self
     */
    public function text(string $text): self
    {
        $this->textBody = $text;
        return $this;
    }

    /**
     * Set body content — auto-detects HTML vs plain text
     *
     * If the content contains HTML tags it is stored as HTML and a plain-text
     * fallback is generated via strip_tags(). Otherwise it is stored as text.
     *
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
     * Populate the body from a named email template
     *
     * Templates live under bootstrap/template/email/ by default.
     *
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
     * Attach a file
     *
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
     * Attach an inline (embedded) image
     *
     * Reference it in HTML as <img src="cid:{$cid}">.
     *
     * @param string      $path     Absolute path to the image file
     * @param string      $cid      Content-ID (e.g. "logo@catalyst")
     * @param string|null $name     Display filename (null = basename)
     * @param string      $mimeType MIME type (empty = auto-detect)
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
     * Mark this message as bulk/marketing email
     *
     * Bulk emails get Precedence: bulk and List-Unsubscribe headers.
     * Transactional emails (default) get X-Priority and Importance headers instead.
     *
     * @return self
     */
    public function bulk(): self
    {
        $this->isBulk = true;
        return $this;
    }

    /**
     * Add a custom mail header
     *
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
     * Dispatch the message via the bound MailManager
     *
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
     * Returns the from value.
     */
    public function getFrom(): ?array
    {
        return $this->from;
    }

    /**
     * Returns the to value.
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * Returns the cc value.
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * Returns the bcc value.
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * Returns the reply to value.
     */
    public function getReplyTo(): ?array
    {
        return $this->replyTo;
    }

    /**
     * Returns the subject value.
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Returns the html body value.
     */
    public function getHtmlBody(): ?string
    {
        return $this->htmlBody;
    }

    /**
     * Returns the text body value.
     */
    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    /**
     * Returns the attachments value.
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * Returns the headers value.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Determines whether is Html.
     */
    public function isHtml(): bool
    {
        return $this->htmlBody !== null;
    }

    /**
     * Determines whether is Bulk.
     */
    public function isBulk(): bool
    {
        return $this->isBulk;
    }

    // --- Internal helpers ----------------------------------------------------

    /**
     * Validate message completeness before sending
     *
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
     * Validate email format and reject known non-deliverable domains
     *
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
     * Append a validated recipient to a list
     *
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
