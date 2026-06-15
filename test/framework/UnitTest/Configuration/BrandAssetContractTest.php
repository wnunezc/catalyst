<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Http\UploadedFile;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class BrandAssetContractTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryFiles = [];

    public function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testFaviconRejectsNonSquareRasterImages(): void
    {
        $file = $this->pngFile(8, 4);
        $upload = new UploadedFile($file, 'favicon.png', 'image/png', filesize($file), UPLOAD_ERR_OK);
        $manager = (new \ReflectionClass(PlatformAppearanceManager::class))->newInstanceWithoutConstructor();

        Assert::same(
            'settings.appearance.messages.favicon_square',
            $manager->brandAssetValidationError($upload, 'favicon')
        );
    }

    public function testFaviconAcceptsSquareRasterImages(): void
    {
        $file = $this->pngFile(8, 8);
        $upload = new UploadedFile($file, 'favicon.png', 'image/png', filesize($file), UPLOAD_ERR_OK);
        $manager = (new \ReflectionClass(PlatformAppearanceManager::class))->newInstanceWithoutConstructor();

        Assert::same(null, $manager->brandAssetValidationError($upload, 'favicon'));
    }

    public function testPrimaryLogoRejectsSvgBecauseItMustRenderInPdfDocuments(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'catalyst-brand-');
        if ($file === false) {
            throw new \RuntimeException('Unable to create temporary SVG.');
        }
        file_put_contents($file, '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 8"></svg>');
        $this->temporaryFiles[] = $file;
        $upload = new UploadedFile($file, 'logo.svg', 'image/svg+xml', filesize($file), UPLOAD_ERR_OK);
        $manager = (new \ReflectionClass(PlatformAppearanceManager::class))->newInstanceWithoutConstructor();

        Assert::same(
            'settings.appearance.messages.primary_logo_raster',
            $manager->brandAssetValidationError($upload, 'logo-primary')
        );
    }

    private function pngFile(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        $file = tempnam(sys_get_temp_dir(), 'catalyst-brand-');
        if ($image === false || $file === false) {
            throw new \RuntimeException('Unable to create temporary PNG.');
        }

        imagepng($image, $file);
        imagedestroy($image);
        $this->temporaryFiles[] = $file;

        return $file;
    }
}
