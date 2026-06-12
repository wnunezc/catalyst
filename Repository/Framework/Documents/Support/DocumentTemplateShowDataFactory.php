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

use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Document\DocumentTemplateRepository;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;

/**
 * Assembles document template detail view data.
 *
 * @package Catalyst\Repository\Documents\Support
 * Responsibility: Combine template state, previews, artifacts, versions, transitions and claims for rendering.
 */
final class DocumentTemplateShowDataFactory
{
    /**
     * Initializes the Document Template Show Data Factory instance.
     *
     * Responsibility: Initializes the Document Template Show Data Factory instance.
     */
    public function __construct(
        private readonly DocumentTemplateRepository $repository,
        private readonly WorkflowManager $workflows,
        private readonly WorkflowRepository $workflowRepository,
        private readonly VersionRepository $versions,
        private readonly DocumentPreviewState $previewState
    ) {
    }

    /**
     * Builds the complete detail-page payload for one document template.
     *
     * Responsibility: Builds the complete detail-page payload for one document template.
     * @param array<string, mixed> $template
     * @param array<string, mixed> $recordPresence
     * @return array<string, mixed>
     */
    public function build(array $template, int $templateId, array $recordPresence): array
    {
        $state = $this->previewState->consume($templateId);
        $payloadJson = $state['payload_json'] ?? json_encode(
            (array) ($template['sample_payload_json'] ?? []),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        $instanceId = (int) ($template['workflow_instance_id'] ?? 0);

        return [
            'title' => __('documents.module.breadcrumb_show'),
            'pageTitle' => (string) ($template['name'] ?? __('documents.show.template_fallback')),
            'template' => $template,
            'preview' => $state['preview'] ?? null,
            'previewPayloadJson' => $payloadJson ?: '{}',
            'versions' => $this->versions->listFor(DocumentTemplateManager::RESOURCE_KEY, $templateId),
            'artifacts' => $this->repository->artifactsForTemplate($templateId),
            'transitions' => $instanceId > 0 ? $this->workflowRepository->transitionsForInstance($instanceId) : [],
            'availableTransitions' => $this->workflows->availableTransitionsForResource(
                DocumentTemplateManager::WORKFLOW_KEY,
                DocumentTemplateManager::RESOURCE_KEY,
                $templateId,
                $template
            ),
            'recordPresence' => $recordPresence,
        ];
    }
}
