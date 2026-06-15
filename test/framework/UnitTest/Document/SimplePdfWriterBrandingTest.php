<?php

declare(strict_types=1);

namespace CatalystTest\Document;

use Catalyst\Framework\Document\Pdf\SimplePdfWriter;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class SimplePdfWriterBrandingTest extends TestCase
{
    private string $temporaryFile = '';

    public function tearDown(): void
    {
        if ($this->temporaryFile !== '' && is_file($this->temporaryFile)) {
            unlink($this->temporaryFile);
        }
    }

    public function testPngBrandLogoIsEmbeddedInPdfWatermark(): void
    {
        $image = imagecreatetruecolor(8, 8);
        $file = tempnam(sys_get_temp_dir(), 'catalyst-pdf-brand-');
        if ($image === false || $file === false) {
            throw new \RuntimeException('Unable to create temporary PNG.');
        }

        imagepng($image, $file);
        imagedestroy($image);
        $this->temporaryFile = $file;

        $pdf = (new SimplePdfWriter())->render('Branding', 'Body', [
            'brand_logo_path' => $file,
            'brand_logo_max_width' => 120,
        ]);

        Assert::contains('/Im1 Do', $pdf);
        Assert::contains('/Filter /DCTDecode', $pdf);
    }
}
