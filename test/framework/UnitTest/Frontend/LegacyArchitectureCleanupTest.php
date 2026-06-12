<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class LegacyArchitectureCleanupTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testOnlyTheNeutralSurfaceStylesheetRemainsActive(): void
    {
        Assert::true(is_file($this->path('public/assets/css/catalyst/surfaces.css')));
        Assert::false(is_file($this->path('public/assets/css/catalyst/admin-surfaces.css')));
        Assert::false(is_file($this->path('public/assets/css/catalyst/admin-layout.css')));
        Assert::false(is_file($this->path('public/assets/css/catalyst/inspinia-product-cutover.css')));

        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $head = $this->read('boot-core/template/_head-assets.phtml');

        Assert::contains("'surfaces_asset_url'", $scope);
        Assert::contains('/assets/css/catalyst/surfaces.css', $scope);
        Assert::contains('{{ surfaces_asset_url }}', $head);
        Assert::false(str_contains($scope, 'admin_surfaces'));
        Assert::false(str_contains($head, 'admin_surfaces'));
    }

    public function testActiveSurfaceSourcesDoNotUseLegacyAdminArchitectureNames(): void
    {
        $source = $this->activeSurfaceSource();

        foreach ([
            'admin-surface',
            'admin-content-shell',
            'admin-page-title',
            'admin-section-card',
            'admin-action-bar',
            'admin-panel-',
            'admin-list-',
            '--catalyst-admin-',
            'data-admin-',
            'demo-ui-shell-body',
            'account-shell-body',
        ] as $legacy) {
            Assert::false(str_contains($source, $legacy), "Active surface source still contains {$legacy}.");
        }
    }

    public function testDevToolsDoesNotExposeTheLegacyTestLayoutAlias(): void
    {
        foreach ([
            'Repository/Framework/DevTools/routes.php',
            'Repository/Framework/DevTools/Views/partials/uml/_header.phtml',
            'app/Framework/Module/ModuleRegistry.php',
        ] as $path) {
            Assert::false(
                str_contains($this->read($path), '/test-layout'),
                "{$path} still exposes the legacy /test-layout alias."
            );
        }
    }

    public function testDemoUiGeneratedPreviewsDoNotUseJavascriptLinks(): void
    {
        $directory = $this->path('Repository/Framework/DemoUi/generated/theme-previews');
        $files = glob($directory . DIRECTORY_SEPARATOR . '*.html') ?: [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            if (!is_string($contents)) {
                throw new \RuntimeException("Unable to read {$file}.");
            }

            Assert::false(
                str_contains($contents, 'javascript:'),
                basename($file) . ' still contains a javascript: link.'
            );
        }
    }

    private function activeSurfaceSource(): string
    {
        $source = '';

        foreach ([
            'Repository/App',
            'Repository/Framework',
            'boot-core/template',
            'public/assets/css/catalyst',
            'public/assets/css/work',
            'public/assets/js/catalyst',
            'public/assets/js/work',
        ] as $relativeDirectory) {
            $directory = $this->path($relativeDirectory);
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                if (str_contains($path, '/template-backup-') || str_contains($path, '/DemoUi/generated/')) {
                    continue;
                }

                if (!in_array($file->getExtension(), ['php', 'phtml', 'css', 'js'], true)) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                if (is_string($contents)) {
                    $source .= "\n" . $contents;
                }
            }
        }

        return $source;
    }

    private function read(string $path): string
    {
        $contents = file_get_contents($this->path($path));

        if (!is_string($contents)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $contents;
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
