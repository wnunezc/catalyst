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

namespace Catalyst\Repository\Documents\Actions;

use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

/**
 * Defines the Document Template Mutation Service class contract.
 *
 * @package Catalyst\Repository\Documents\Actions
 * Responsibility: Coordinates the document template mutation service behavior within its module boundary.
 */
final class DocumentTemplateMutationService
{
    private Logger $logger;

    /**
     * Initializes the Document Template Mutation Service instance.
     */
    public function __construct(
        private readonly DocumentTemplateManager $manager,
        private readonly DocumentTemplateExportService $exportService
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

    /**
     * Handles the delete workflow.
     */
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
        $this->exportService->export($template, $payload);
    }

    /**
     * Handles the transition workflow.
     */
    public function transition(DocumentTemplate $template, Request $request, string $transition, ?string $notes): void
    {
        $this->assertClaim((int) $template->getKey(), $request);
        $this->manager->transition($template, $transition, $notes);
    }

    /**
     * Handles the restore version workflow.
     */
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

    /**
     * Handles the assert claim workflow.
     */
    private function assertClaim(int $templateId, Request $request): void
    {
        $claimToken = trim((string) $request->input('claim_token', ''));

        RecordClaimManager::getInstance()->assertAvailable(
            resourceKey: DocumentTemplateManager::RESOURCE_KEY,
            recordId: $templateId,
            claimToken: $claimToken !== '' ? $claimToken : null
        );
    }

    /**
     * Handles the release claim workflow.
     */
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
