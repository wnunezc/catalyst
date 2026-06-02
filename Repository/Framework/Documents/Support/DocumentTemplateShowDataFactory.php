<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Support;

use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Document\DocumentTemplateRepository;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;

final class DocumentTemplateShowDataFactory
{
    public function __construct(
        private readonly DocumentTemplateRepository $repository,
        private readonly WorkflowManager $workflows,
        private readonly WorkflowRepository $workflowRepository,
        private readonly VersionRepository $versions,
        private readonly DocumentPreviewState $previewState
    ) {
    }

    /**
     * @param array<string, mixed> $template
     * @param array<string, mixed> $claimContext
     * @return array<string, mixed>
     */
    public function build(array $template, int $templateId, array $claimContext): array
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
            'claimContext' => $claimContext,
        ];
    }
}
