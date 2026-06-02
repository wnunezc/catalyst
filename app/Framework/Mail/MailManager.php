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

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Exceptions\MailException;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Mail manager — singleton gateway to PHPMailer.
 *
 * Reads SMTP configuration from the effective runtime config
 * (mail.json → .env fallback) and exposes a fluent message factory.
 *
 * @package  Catalyst\Framework\Mail
 * @since    1.0.0
 *
 * @uses     PHPMailer
 * @uses     SingletonTrait
 *
 * @throws   MailException
 */
class MailManager
{
    use SingletonTrait;

    /**
     * @var array Resolved SMTP configuration
     */
    protected array $config = [
        'host'                   => '',
        'port'                   => 587,
        'username'               => '',
        'password'               => '',
        'encryption'             => 'tls',
        'auth'                   => true,
        'from_address'           => '',
        'from_name'              => '',
        'reply_to'               => '',
        'verify_peer'            => true,
        'verify_peer_name'       => true,
        'allow_self_signed'      => false,
        'dkim_enabled'           => false,
        'dkim_domain'            => '',
        'dkim_selector'          => '',
        'dkim_private_key'       => '',
        'dkim_passphrase'        => '',
        'humanitarian_enabled'   => false,
        'humanitarian_purpose'   => 'Non-commercial',
        'humanitarian_contact'   => '',
    ];

    /**
     * @var bool Whether init() has already run
     */
    protected bool $initialized = false;

    /**
     * Load SMTP configuration from GET_ENV_VAR and validate it
     *
     * Calling init() a second time is a no-op (idempotent).
     *
     * @param array $override Optional array to override individual config keys
     * @return self
     * @throws MailException If host or username are missing after loading
     */
    public function init(array $override = []): self
    {
        if ($this->initialized) {
            return $this;
        }

        $env = defined('GET_ENV_VAR') ? GET_ENV_VAR : [];

        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();
            $mailConfig    = $configManager instanceof ConfigManager
                ? $configManager->entry('mail', 'mail1')
                : [];
        } catch (\Throwable) {
            $mailConfig = [];
        }

        $this->config = array_merge($this->config, [
            'host'                 => $mailConfig['mail_host']         ?? '',
            'port'                 => (int)($mailConfig['mail_port']   ?? 587),
            'username'             => $mailConfig['mail_username']     ?? '',
            'password'             => $mailConfig['mail_password']     ?? '',
            'encryption'           => $mailConfig['mail_encryption']   ?? 'tls',
            'from_address'         => $mailConfig['mail_from_address'] ?? '',
            'from_name'            => $mailConfig['mail_from_name']    ?? '',
            'dkim_enabled'         => filter_var($env['DKIM_ENABLED']         ?? false, FILTER_VALIDATE_BOOLEAN),
            'dkim_domain'          => $env['DKIM_DOMAIN']            ?? '',
            'dkim_selector'        => $env['DKIM_SELECTOR']          ?? '',
            'dkim_private_key'     => $env['DKIM_PRIVATE_KEY_PATH']  ?? '',
            'humanitarian_enabled' => filter_var($env['HUMANITARIAN_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'humanitarian_purpose' => $env['HUMANITARIAN_PURPOSE']   ?? 'Non-commercial',
            'humanitarian_contact' => $env['HUMANITARIAN_CONTACT']   ?? '',
        ]);

        if (!empty($override)) {
            $this->config = array_merge($this->config, $override);
        }

        if (empty($this->config['host']) || empty($this->config['username'])) {
            throw MailException::configurationError(
                'Mail configuration is incomplete. Configure mail.json via /configuration/environment-setup or provide .env fallback values.'
            );
        }

        $this->initialized = true;
        return $this;
    }

    /**
     * Create a new MailMessage bound to this manager
     *
     * @return MailMessage
     * @throws MailException If the manager has not been initialized
     */
    public function createMessage(): MailMessage
    {
        $this->ensureInitialized();
        return new MailMessage($this);
    }

    /**
     * Send a prepared MailMessage via PHPMailer
     *
     * @param MailMessage $message Validated message to send
     * @return bool True on success
     * @throws MailException On configuration error or SMTP failure
     */
    public function send(MailMessage $message): bool
    {
        $this->ensureInitialized();

        $mailer = $this->createMailer();
        $this->configureMailer($mailer);

        try {
            // Override from-address if the message specifies one
            $from = $message->getFrom();
            if ($from !== null) {
                $mailer->setFrom($from['email'], $from['name'] ?? '');
            }

            // Recipients
            foreach ($message->getTo() as $r) {
                $mailer->addAddress($r['email'], $r['name'] ?? '');
            }
            foreach ($message->getCc() as $r) {
                $mailer->addCC($r['email'], $r['name'] ?? '');
            }
            foreach ($message->getBcc() as $r) {
                $mailer->addBCC($r['email'], $r['name'] ?? '');
            }

            // Reply-to
            $replyTo = $message->getReplyTo();
            if (!empty($replyTo)) {
                $mailer->addReplyTo($replyTo['email'], $replyTo['name'] ?? '');
            } elseif (!empty($this->config['reply_to'])) {
                $mailer->addReplyTo($this->config['reply_to']);
            }

            // Subject
            $mailer->Subject = $message->getSubject();

            // Body
            if ($message->isHtml()) {
                $mailer->isHTML();
                $mailer->Body    = $message->getHtmlBody();
                $mailer->AltBody = $message->getTextBody() ?: $this->htmlToText($message->getHtmlBody());
            } else {
                $mailer->isHTML(false);
                $mailer->Body = $message->getTextBody();
            }

            // Attachments
            foreach ($message->getAttachments() as $a) {
                if ($a['inline']) {
                    $mailer->addEmbeddedImage(
                        $a['path'],
                        $a['cid'],
                        $a['name'],
                        $a['encoding'],
                        $a['type']
                    );
                } else {
                    $mailer->addAttachment($a['path'], $a['name'], $a['encoding'], $a['type']);
                }
            }

            // Custom Message-ID (avoids leaking the internal hostname)
            $randomId          = bin2hex(random_bytes(16));
            $fromAddress       = $this->config['from_address'];
            $domain            = $fromAddress ? substr(strrchr($fromAddress, '@'), 1) : 'catalyst.local';
            $mailer->MessageID = "<$randomId@$domain>";
            $mailer->XMailer   = ' ';

            // Organization header (identifies sender application)
            try {
                $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();
                $appName       = $configManager instanceof ConfigManager
                    ? (string)($configManager->entry('app', 'project')['project_name'] ?? 'Catalyst Framework')
                    : 'Catalyst Framework';
            } catch (\Throwable) {
                $appName = 'Catalyst Framework';
            }
            $mailer->addCustomHeader('Organization', $appName);

            // Always suppress auto-replies (OOF, vacation replies)
            $mailer->addCustomHeader('X-Auto-Response-Suppress', 'OOF, AutoReply');

            // Bulk vs transactional distinction
            if ($message->isBulk()) {
                // Bulk/marketing: signal to spam filters + provide unsubscribe
                $mailer->addCustomHeader('Precedence', 'bulk');
                $mailer->addCustomHeader('List-Unsubscribe', '<mailto:' . $this->config['from_address'] . '?subject=unsubscribe>');
                $mailer->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            } else {
                // Transactional: normal priority, no bulk signals
                $mailer->addCustomHeader('X-Priority', '3');
                $mailer->addCustomHeader('Importance', 'Normal');
            }

            // Humanitarian headers (opt-in via HUMANITARIAN_ENABLED=true)
            if ($this->config['humanitarian_enabled']) {
                $appDomain = 'https://' . $domain;
                $mailer->addCustomHeader('X-Humanitarian-Protection', 'This site is protected by international humanitarian law.');
                $mailer->addCustomHeader('X-Humanitarian-Purpose', $this->config['humanitarian_purpose']);
                $mailer->addCustomHeader('X-Humanitarian-Licenced', $appDomain . '/');
                if (!empty($this->config['humanitarian_contact'])) {
                    $mailer->addCustomHeader('X-Humanitarian-Contact', $this->config['humanitarian_contact']);
                }
            }

            // Custom headers from the message
            foreach ($message->getHeaders() as $headerName => $headerValue) {
                $mailer->addCustomHeader($headerName, $headerValue);
            }

            // DKIM signing (optional)
            $this->configureDkim($mailer);

            if (!$mailer->send()) {
                throw MailException::sendingError($mailer->ErrorInfo);
            }

            return true;
        } catch (PHPMailerException $e) {
            throw MailException::sendingError($e->getMessage());
        }
    }

    /**
     * Test the SMTP connection by sending a probe email
     *
     * @param string $testRecipient Address to receive the test email
     * @return array{success: bool, message: string}
     */
    public function testConnection(string $testRecipient = ''): array
    {
        try {
            $this->ensureInitialized();

            $to = $testRecipient ?: $this->config['from_address'];

            $this->createMessage()
                ->to($to)
                ->subject('Catalyst Mail — Connection Test')
                ->body('<h1>Connection OK</h1><p>This is an automated test from Catalyst Framework.</p>')
                ->send();

            return ['success' => true, 'message' => 'Test email sent successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Return the resolved configuration array (passwords redacted)
     *
     * @return array
     */
    public function getConfig(): array
    {
        $safe             = $this->config;
        $safe['password'] = $safe['password'] !== '' ? '***' : '';
        return $safe;
    }

    /**
     * Convert an HTML body to a plain-text alternative (AltBody)
     *
     * Strips style/script blocks, HTML tags, decodes entities, and collapses whitespace.
     *
     * @param string $html
     * @return string
     */
    protected function htmlToText(string $html): string
    {
        $text = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? $html;
        $text = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/(\r\n|\r|\n){2,}/', "\n\n", $text) ?? $text;
        return trim($text);
    }

    /**
     * Create a bare PHPMailer instance with exceptions enabled
     *
     * @return PHPMailer
     */
    protected function createMailer(): PHPMailer
    {
        return new PHPMailer(true);
    }

    /**
     * Apply SMTP settings to a PHPMailer instance
     *
     * @param PHPMailer $mailer
     * @throws PHPMailerException
     */
    protected function configureMailer(PHPMailer $mailer): void
    {
        $mailer->isSMTP();
        $mailer->Host       = $this->config['host'];
        $mailer->Port       = $this->config['port'];
        $mailer->SMTPSecure = $this->config['encryption'];
        $mailer->CharSet    = PHPMailer::CHARSET_UTF8;
        $mailer->SMTPDebug  = 0;

        if ($this->config['auth']) {
            $mailer->SMTPAuth = true;
            $mailer->Username = $this->config['username'];
            $mailer->Password = $this->config['password'];
        } else {
            $mailer->SMTPAuth = false;
        }

        $mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => $this->config['verify_peer'],
                'verify_peer_name'  => $this->config['verify_peer_name'],
                'allow_self_signed' => $this->config['allow_self_signed'],
            ],
        ];

        // Quoted-printable is safer than 8bit for international characters and spam filters
        $mailer->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;

        // Sender sets Return-Path (bounce address) — helps with deliverability
        $mailer->Sender = $this->config['from_address'];

        $mailer->setFrom($this->config['from_address'], $this->config['from_name']);
    }

    /**
     * Configure DKIM signing when DKIM_ENABLED=true and the key file exists
     *
     * Failures are silently skipped — the email is still sent without DKIM.
     *
     * @param PHPMailer $mailer
     */
    protected function configureDkim(PHPMailer $mailer): void
    {
        if (!$this->config['dkim_enabled']) {
            return;
        }

        $keyPath = $this->config['dkim_private_key'];

        if (empty($this->config['dkim_selector']) || empty($keyPath) || !file_exists($keyPath)) {
            return;
        }

        try {
            $privateKey = file_get_contents($keyPath);

            $mailer->DKIM_domain     = $this->config['dkim_domain'];
            $mailer->DKIM_private    = $privateKey;
            $mailer->DKIM_selector   = $this->config['dkim_selector'];
            $mailer->DKIM_passphrase = $this->config['dkim_passphrase'];
            $mailer->DKIM_identity   = $this->config['from_address'];
        } catch (Exception) {
            // Non-fatal: DKIM signing skipped
        }
    }

    /**
     * Trigger auto-initialization if init() has not been called yet
     *
     * @throws MailException
     */
    protected function ensureInitialized(): void
    {
        if (!$this->initialized) {
            $this->init();
        }
    }
}
