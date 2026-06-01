<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Actions;

use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

final class DocumentTemplateMutationService
{
    private Logger $logger;

    public function __construct(
        private readonly DocumentTemplateManager $manager
    ) {
        $this->logger = Logger::getInstance();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(DocumentTemplate $template, Request $request, array $payload): void
    {
        $templateId = (int) $template->getKey();
        $this->assertClaim($templateId, $request);

        $template->fill([
            'lock_version' => max(1, (int) $request->input('lock_version', $template->toArray()['lock_version'] ?? 1)),
        ]);

        $this->manager->update($template, $payload);
        $this->releaseClaim($templateId, $request, 'document template updated');
    }

    public function delete(DocumentTemplate $template, Request $request): void
    {
        $templateId = (int) $template->getKey();
        $this->assertClaim($templateId, $request);
        $this->manager->delete($template);
        $this->releaseClaim($templateId, $request, 'document template deleted');
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function export(DocumentTemplate $template, Request $request, array $payload): void
    {
        $this->assertClaim((int) $template->getKey(), $request);
        $this->manager->export($template, $payload);
    }

    public function transition(DocumentTemplate $template, Request $request, string $transition, ?string $notes): void
    {
        $this->assertClaim((int) $template->getKey(), $request);
        $this->manager->transition($template, $transition, $notes);
    }

    public function restoreVersion(DocumentTemplate $template, Request $request, int $versionId): void
    {
        $templateId = (int) $template->getKey();
        $this->assertClaim($templateId, $request);
        $versionManager = VersionManager::getInstance();
        $restored = $versionManager->restore($versionId);
        $versionManager->capture(
            DocumentTemplateManager::RESOURCE_KEY,
            $templateId,
            $restored,
            __('documents.messages.template_restored_summary') . ' ' . $versionId
        );
    }

    private function assertClaim(int $templateId, Request $request): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        RecordClaimManager::getInstance()->assertAvailable(
            resourceKey: DocumentTemplateManager::RESOURCE_KEY,
            recordId: $templateId,
            claimToken: $claimToken !== '' ? $claimToken : null
        );
    }

    private function releaseClaim(int $templateId, Request $request, ?string $reason = null): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        try {
            RecordClaimManager::getInstance()->release(
                resourceKey: DocumentTemplateManager::RESOURCE_KEY,
                recordId: $templateId,
                reason: $reason,
                claimToken: $claimToken !== '' ? $claimToken : null
            );
        } catch (Throwable $e) {
            $this->logger->warning('Document claim release skipped after mutation.', [
                'record_id' => $templateId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
