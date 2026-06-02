<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Support;

use Catalyst\Framework\Session\SessionManager;

final class DocumentPreviewState
{
    /**
     * @param array<string, mixed> $preview
     */
    public function stash(int $templateId, array $preview, string $payloadJson): void
    {
        SessionManager::getInstance()->set('_document_template_preview_state', [
            'template_id' => $templateId,
            'preview' => $preview,
            'payload_json' => $payloadJson,
        ]);
    }

    /**
     * @return array{preview: array<string, mixed>|null, payload_json: string}|null
     */
    public function consume(int $templateId): ?array
    {
        $session = SessionManager::getInstance();
        $state = $session->get('_document_template_preview_state');

        if (!is_array($state) || (int) ($state['template_id'] ?? 0) !== $templateId) {
            return null;
        }

        $session->remove('_document_template_preview_state');

        return [
            'preview' => is_array($state['preview'] ?? null) ? $state['preview'] : null,
            'payload_json' => (string) ($state['payload_json'] ?? '{}'),
        ];
    }
}
