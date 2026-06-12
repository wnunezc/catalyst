<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use Catalyst\Framework\View\AssetUrl;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class AssetUrlTest extends TestCase
{
    private string $publicRoot;

    public function setUp(): void
    {
        $this->publicRoot = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'public';
    }

    public function testLocalAssetUsesPublishedFileModificationTime(): void
    {
        $relativePath = '/assets/js/catalyst/runtime/ui-runtime.js';
        $filesystemPath = $this->publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $expectedVersion = (string) filemtime($filesystemPath);

        Assert::same(
            $relativePath . '?v=' . rawurlencode($expectedVersion),
            AssetUrl::versioned($relativePath, $this->publicRoot)
        );
    }

    public function testExistingQueryAndFragmentArePreserved(): void
    {
        $relativePath = '/assets/css/catalyst/status-bar.css';
        $filesystemPath = $this->publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $expectedVersion = (string) filemtime($filesystemPath);

        Assert::same(
            $relativePath . '?media=screen&v=' . rawurlencode($expectedVersion) . '#status',
            AssetUrl::versioned($relativePath . '?media=screen#status', $this->publicRoot)
        );
    }

    public function testMissingLocalAssetUsesStableZeroVersion(): void
    {
        Assert::same(
            '/assets/js/work/missing/script.js?v=0',
            AssetUrl::versioned('/assets/js/work/missing/script.js', $this->publicRoot)
        );
    }

    public function testExternalUrlsAreNotRewritten(): void
    {
        Assert::same(
            'https://cdn.example.test/app.js',
            AssetUrl::versioned('https://cdn.example.test/app.js', $this->publicRoot)
        );
    }

    public function testDocumentScopeVersionsPublishedWorkAssets(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains(
            'AssetUrl::versioned("/assets/css/work/{$slug}/style.css")',
            $scope
        );
        Assert::contains(
            'AssetUrl::versioned("/assets/js/work/{$slug}/script.js")',
            $scope
        );
    }

    public function testSharedTemplatesConsumeVersionedAssetUrls(): void
    {
        $head = $this->read('boot-core/template/_head-assets.phtml');
        $body = $this->read('boot-core/template/_body-scripts.phtml');

        Assert::contains('{{ appearance_bootstrap_asset_url }}', $head);
        Assert::contains('{{ status_bar_asset_url }}', $head);
        Assert::contains('{{ bootstrap_bundle_asset_url }}', $body);
        Assert::contains('{{ ui_runtime_asset_url }}', $body);
    }

    private function read(string $path): string
    {
        $source = file_get_contents(
            dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
        );

        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
