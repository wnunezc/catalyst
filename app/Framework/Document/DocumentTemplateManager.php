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

namespace Catalyst\Framework\Document;

use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Document\Pdf\PdfRendererInterface;
use Catalyst\Framework\Document\Pdf\SimplePdfWriter;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Framework\View\HtmlAllowlistSanitizer;
use Catalyst\Framework\Workflow\WorkflowManager;
use RuntimeException;

/**
 * Defines the Document Template Manager class contract.
 *
 * @package Catalyst\Framework\Document
 * Responsibility: Coordinates the document template manager behavior within its module boundary.
 */
final class DocumentTemplateManager
{
    use SingletonTrait;

    public const RESOURCE_KEY = 'document-templates';
    public const WORKFLOW_KEY = 'document-templates.lifecycle';

    private TemplateStringRenderer $renderer;
    private PdfRendererInterface $pdfWriter;
    private StorageManager $storage;
    private WorkflowManager $workflows;
    private VersionManager $versions;
    private PlatformAppearanceManager $appearance;
    private HtmlAllowlistSanitizer $htmlSanitizer;

    /**
     * Initializes the Document Template Manager instance.
     */
    protected function __construct()
    {
        $this->renderer = new TemplateStringRenderer();
        $this->pdfWriter = new SimplePdfWriter();
        $this->storage = StorageManager::getInstance();
        $this->workflows = WorkflowManager::getInstance();
        $this->versions = VersionManager::getInstance();
        $this->appearance = PlatformAppearanceManager::getInstance();
        $this->htmlSanitizer = new HtmlAllowlistSanitizer();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): DocumentTemplate
    {
        $template = DocumentTemplate::create([
            'name' => trim((string) ($payload['name'] ?? '')),
            'slug' => trim((string) ($payload['slug'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'format' => trim((string) ($payload['format'] ?? 'html')) ?: 'html',
            'variables_schema_json' => $this->decodeJsonField($payload['variables_schema_json'] ?? '[]'),
            'sample_payload_json' => $this->decodeJsonField($payload['sample_payload_json'] ?? '{}'),
            'body_template' => (string) ($payload['body_template'] ?? ''),
        ]);

        $this->workflows->ensureInstance(self::WORKFLOW_KEY, self::RESOURCE_KEY, (int) $template->getKey());
        $this->versions->capture(self::RESOURCE_KEY, (int) $template->getKey(), $template->toArray(), 'Template created');

        return $template;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(DocumentTemplate $template, array $payload): DocumentTemplate
    {
        $template->fill([
            'name' => trim((string) ($payload['name'] ?? '')),
            'slug' => trim((string) ($payload['slug'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'format' => trim((string) ($payload['format'] ?? 'html')) ?: 'html',
            'variables_schema_json' => $this->decodeJsonField($payload['variables_schema_json'] ?? '[]'),
            'sample_payload_json' => $this->decodeJsonField($payload['sample_payload_json'] ?? '{}'),
            'body_template' => (string) ($payload['body_template'] ?? ''),
        ]);
        $template->save();

        $this->versions->capture(self::RESOURCE_KEY, (int) $template->getKey(), $template->toArray(), 'Template updated');

        return $template;
    }

    /**
     * Handles the delete workflow.
     */
    public function delete(DocumentTemplate $template): void
    {
        $template->delete();
    }

    /**
     * Handles the archive artifact workflow.
     */
    public function archiveArtifact(DocumentArtifact $artifact): DocumentArtifact
    {
        if (!empty($artifact->toArray()['archived_at'])) {
            return $artifact;
        }

        $artifact->fill([
            'archived_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $artifact->save();

        return $artifact;
    }

    /**
     * Handles the purge artifact workflow.
     */
    public function purgeArtifact(DocumentArtifact $artifact): void
    {
        $snapshot = $artifact->toArray();
        $path = trim((string) ($snapshot['path'] ?? ''));
        $disk = trim((string) ($snapshot['disk'] ?? 'local')) ?: 'local';

        if ($path !== '') {
            $this->storage->delete($path, $disk);
        }

        $artifact->delete();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function preview(DocumentTemplate $template, array $payload = []): array
    {
        $resolvedPayload = $payload !== [] ? $payload : (array) ($template->toArray()['sample_payload_json'] ?? []);
        $rendered = $this->renderer->render((string) ($template->toArray()['body_template'] ?? ''), $resolvedPayload);
        $format = (string) ($template->toArray()['format'] ?? 'html');
        $displayContent = $format === 'html'
            ? $this->htmlSanitizer->sanitize($rendered)
            : ($format === 'pdf' ? $this->normalizePdfText($rendered) : $rendered);

        return [
            'content' => $displayContent,
            'rendered_source' => $rendered,
            'checksum_sha256' => hash('sha256', $rendered),
            'payload' => $resolvedPayload,
            'format' => $format,
            'display_content' => $displayContent,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function export(DocumentTemplate $template, array $payload = []): DocumentArtifact
    {
        $preview = $this->preview($template, $payload);
        $templateData = $template->toArray();
        $format = 'pdf';
        $extension = 'pdf';
        $slug = trim((string) ($templateData['slug'] ?? 'template')) ?: 'template';
        $path = sprintf(
            'generated-documents/%s/%s-%s.%s',
            $slug,
            date('YmdHis'),
            substr(hash('sha1', $preview['checksum_sha256'] . microtime(true)), 0, 12),
            $extension
        );

        $renderedSource = (string) ($preview['rendered_source'] ?? $preview['content'] ?? '');
        $normalizedText = $this->normalizePdfText($renderedSource);
        $content = $this->pdfWriter->render(
            trim((string) ($templateData['name'] ?? 'Document export')),
            $normalizedText,
            $this->appearance->pdfWatermarkSettings()
        );

        $storedPath = $this->storage->put($path, $content, 'local');

        return DocumentArtifact::create([
            'document_template_id' => (int) $template->getKey(),
            'workflow_instance_id' => $this->workflowInstanceId((int) $template->getKey()),
            'name' => trim((string) ($templateData['name'] ?? 'Document template')) . ' export',
            'format' => $format,
            'disk' => 'local',
            'path' => $storedPath,
            'public_url' => $this->storage->url($storedPath, 'local'),
            'checksum_sha256' => (string) ($preview['checksum_sha256'] ?? ''),
            'payload_snapshot_json' => (array) ($preview['payload'] ?? []),
            'rendered_content' => $normalizedText,
        ]);
    }

    /**
     * Handles the transition workflow.
     */
    public function transition(DocumentTemplate $template, string $transitionKey, ?string $notes = null): array
    {
        return $this->workflows->transition(
            self::WORKFLOW_KEY,
            self::RESOURCE_KEY,
            (int) $template->getKey(),
            $transitionKey,
            record: $template,
            notes: $notes
        );
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>|array<int, mixed>
     */
    private function decodeJsonField(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Handles the workflow instance id workflow.
     */
    private function workflowInstanceId(int $recordId): ?int
    {
        $instance = $this->workflows->ensureInstance(self::WORKFLOW_KEY, self::RESOURCE_KEY, $recordId);
        $id = $instance['id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    /**
     * Normalizes the provided value.
     */
    private function normalizePdfText(string $content): string
    {
        $normalized = preg_replace('/<\s*br\s*\/?>/i', "\n", $content) ?? $content;
        $normalized = preg_replace('/<\s*\/p\s*>/i', "\n\n", $normalized) ?? $normalized;
        $normalized = preg_replace('/<\s*\/div\s*>/i', "\n", $normalized) ?? $normalized;
        $normalized = preg_replace('/<\s*\/li\s*>/i', "\n", $normalized) ?? $normalized;
        $normalized = strip_tags($normalized);
        $normalized = html_entity_decode($normalized, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace("/\n{3,}/", "\n\n", $normalized) ?? $normalized;

        return trim($normalized);
    }
}
