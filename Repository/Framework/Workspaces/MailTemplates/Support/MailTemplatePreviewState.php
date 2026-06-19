<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\MailTemplates\Support;

use Catalyst\Framework\Session\SessionManager;

/**
 * Carries one rendered mail template preview across the Catalyst form redirect.
 */
final class MailTemplatePreviewState
{
    /**
     * @param array<string, mixed> $preview
     */
    public function stash(string $key, array $preview, string $payloadJson, string $locale): void
    {
        SessionManager::getInstance()->set('_mail_template_preview_state', [
            'key' => $key,
            'locale' => $locale,
            'preview' => $preview,
            'payload_json' => $payloadJson,
        ]);
    }

    /**
     * @return array{preview: array<string, mixed>|null, payload_json: string}|null
     */
    public function consume(string $key, string $locale): ?array
    {
        $session = SessionManager::getInstance();
        $state = $session->get('_mail_template_preview_state');

        if (
            !is_array($state)
            || (string) ($state['key'] ?? '') !== $key
            || (string) ($state['locale'] ?? '') !== $locale
        ) {
            return null;
        }

        $session->remove('_mail_template_preview_state');

        return [
            'preview' => is_array($state['preview'] ?? null) ? $state['preview'] : null,
            'payload_json' => (string) ($state['payload_json'] ?? '{}'),
        ];
    }
}
