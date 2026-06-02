<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Actions;

use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Document\DocumentTemplateManager;

final class DocumentTemplateExportService
{
    public function __construct(
        private readonly DocumentTemplateManager $manager
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function export(DocumentTemplate $template, array $payload): DocumentArtifact
    {
        return $this->manager->export($template, $payload);
    }
}
