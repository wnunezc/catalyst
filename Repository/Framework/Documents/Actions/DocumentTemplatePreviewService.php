<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Actions;

use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Document\DocumentTemplateManager;

final class DocumentTemplatePreviewService
{
    public function __construct(
        private readonly DocumentTemplateManager $manager
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function preview(DocumentTemplate $template, array $payload): array
    {
        return $this->manager->preview($template, $payload);
    }
}
