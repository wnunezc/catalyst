<?php

declare(strict_types=1);

namespace Catalyst\Framework\Document\Pdf;

interface PdfRendererInterface
{
    /**
     * @param array<string, mixed> $watermark
     */
    public function render(string $title, string $body, array $watermark = []): string;
}
