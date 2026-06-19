<?php

declare(strict_types=1);

namespace Catalyst\Framework\Mail;

use Catalyst\Helpers\Log\Logger;
use Throwable;

final class OutboundEmailService
{
    /**
     * @param callable|null $transport Receives rendered message context and throws on failure.
     */
    public function __construct(
        private readonly EmailTemplateManager $templates = new EmailTemplateManager(),
        private readonly mixed $transport = null
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{sent:bool, template:string, locale:string, message:string}
     */
    public function sendTemplate(
        string $template,
        string $recipientEmail,
        string $recipientName,
        array $payload,
        string $locale = 'en'
    ): array {
        $rendered = null;

        try {
            $rendered = $this->templates->render($template, $payload, $locale);
            if (is_callable($this->transport)) {
                ($this->transport)($rendered, $recipientEmail, $recipientName);
            } else {
                MailManager::getInstance()
                    ->init()
                    ->createMessage()
                    ->to($recipientEmail, $recipientName)
                    ->subject($rendered['subject'])
                    ->html($rendered['html'])
                    ->text($rendered['text'])
                    ->send();
            }

            $this->logDelivery('sent', $template, $recipientEmail, $rendered['locale']);

            return [
                'sent' => true,
                'template' => $template,
                'locale' => $rendered['locale'],
                'message' => 'Email delivered.',
            ];
        } catch (Throwable $e) {
            $resolvedLocale = is_array($rendered) ? (string) ($rendered['locale'] ?? $locale) : $locale;
            $this->logDelivery('failed', $template, $recipientEmail, $resolvedLocale, $e);

            return [
                'sent' => false,
                'template' => $template,
                'locale' => $resolvedLocale,
                'message' => 'Email delivery failed.',
            ];
        }
    }

    private function logDelivery(
        string $status,
        string $template,
        string $recipientEmail,
        string $locale,
        ?Throwable $error = null
    ): void {
        try {
            Logger::getInstance()->info('Outbound email delivery ' . $status, [
                'template' => $template,
                'locale' => $locale,
                'recipient_hash' => hash('sha256', strtolower(trim($recipientEmail))),
                'error_class' => $error !== null ? $error::class : null,
            ]);
        } catch (Throwable) {
        }
    }
}
