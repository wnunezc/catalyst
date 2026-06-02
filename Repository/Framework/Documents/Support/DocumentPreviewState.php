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

namespace Catalyst\Repository\Documents\Support;

use Catalyst\Framework\Session\SessionManager;

/**
 * Stores the most recent document preview in the session.
 *
 * @package Catalyst\Repository\Documents\Support
 * Responsibility: Carry one rendered preview and its payload across the redirect to the detail view.
 */
final class DocumentPreviewState
{
    /**
     * Stores a rendered preview for the selected document template.
     *
     * Responsibility: Stores a rendered preview for the selected document template.
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
     * Consumes the pending preview when it belongs to the selected document template.
     *
     * Responsibility: Consumes the pending preview when it belongs to the selected document template.
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
