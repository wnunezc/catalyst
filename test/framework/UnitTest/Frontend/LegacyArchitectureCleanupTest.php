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

    public function testActiveSurfacesDoNotKeepAlternativeCardAliases(): void
    {
        $source = $this->activeVisualSource();

        foreach ([
            'account-card',
            'auth-card',
            'card-overlay',
            'catalyst-card',
            'catalyst-error-card',
            'datagrid-card',
            'rbac-permission-card',
            'settings-config-card',
            'surface-section-card',
            'surface-metric-card',
            'tf-card',
            'ui-surface-card',
            'uml-layer-card',
            'operations-card',
        ] as $alias) {
            Assert::false(str_contains($source, $alias), "Active surface source still contains {$alias}.");
        }
    }

    public function testActiveTemplatesOnlyUseCanonicalCardClasses(): void
    {
        $allowed = [
            'card',
            'card-body',
            'card-footer',
            'card-group',
            'card-header',
            'card-header-pills',
            'card-header-tabs',
            'card-img',
            'card-img-bottom',
            'card-img-overlay',
            'card-img-top',
            'card-link',
            'card-radio',
            'card-subtitle',
            'card-text',
            'card-title',
        ];

        foreach ([
            'Repository/App',
            'Repository/Framework',
            'boot-core/template',
        ] as $relativeDirectory) {
            $directory = $this->path($relativeDirectory);
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'phtml') {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                if (
                    str_contains($path, '/DemoUi/')
                    || str_contains($path, '/template-backup-')
                    || str_ends_with($path, '/public/assets/css/catalyst/inspinia-shell.css')
                ) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                if (!is_string($contents) || preg_match_all('/class="([^"]+)"/', $contents, $matches) === false) {
                    continue;
                }

                foreach ($matches[1] as $classList) {
                    foreach (preg_split('/\s+/', $classList) ?: [] as $class) {
                        if ($class === '' || !str_contains($class, 'card') || str_contains($class, '{{')) {
                            continue;
                        }

                        Assert::true(
                            in_array($class, $allowed, true),
                            "{$path} uses non-canonical card class {$class}."
                        );
                    }
                }
            }
        }
    }

    public function testActiveStylesheetsOnlyTargetCanonicalCardClasses(): void
    {
        $allowed = [
            'card',
            'card-body',
            'card-footer',
            'card-group',
            'card-header',
            'card-header-pills',
            'card-header-tabs',
            'card-img',
            'card-img-bottom',
            'card-img-overlay',
            'card-img-top',
            'card-link',
            'card-radio',
            'card-subtitle',
            'card-text',
            'card-title',
        ];

        foreach ([
            'Repository/App',
            'Repository/Framework',
            'public/assets/css/catalyst',
            'public/assets/css/work',
        ] as $relativeDirectory) {
            $directory = $this->path($relativeDirectory);
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'css') {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                if (str_contains($path, '/DemoUi/') || str_ends_with($path, '/public/assets/css/catalyst/inspinia-shell.css')) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                if (!is_string($contents) || preg_match_all('/\.([a-zA-Z_][a-zA-Z0-9_-]*card[a-zA-Z0-9_-]*)/', $contents, $matches) === false) {
                    continue;
                }

                foreach (array_unique($matches[1]) as $class) {
                    Assert::true(
                        in_array($class, $allowed, true),
                        "{$path} targets non-canonical card class {$class}."
                    );
                }
            }
        }
    }

    public function testCardRegionsHaveANativeCardAncestor(): void
    {
        foreach ([
            'Repository/App',
            'Repository/Framework',
            'boot-core/template',
        ] as $relativeDirectory) {
            $directory = $this->path($relativeDirectory);
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'phtml') {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                if (str_contains($path, '/DemoUi/') || str_contains($path, '/template-backup-')) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                if (!is_string($contents) || !preg_match('/card-(?:header|body|footer)/', $contents)) {
                    continue;
                }

                $document = new \DOMDocument();
                $previous = libxml_use_internal_errors(true);
                $document->loadHTML('<main>' . $contents . '</main>');
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
                $xpath = new \DOMXPath($document);
                $regions = $xpath->query(
                    '//*[contains(concat(" ", normalize-space(@class), " "), " card-header ")'
                    . ' or contains(concat(" ", normalize-space(@class), " "), " card-body ")'
                    . ' or contains(concat(" ", normalize-space(@class), " "), " card-footer ")]'
                );

                if (!$regions instanceof \DOMNodeList) {
                    continue;
                }

                foreach ($regions as $region) {
                    $card = $xpath->query(
                        'ancestor-or-self::*[contains(concat(" ", normalize-space(@class), " "), " card ")][1]',
                        $region
                    );
                    Assert::true(
                        $card instanceof \DOMNodeList && $card->length === 1,
                        "{$path} contains a card region without a native .card ancestor."
                    );
                }
            }
        }
    }

    public function testActiveSurfacesDoNotUseNamedSurfaceProfiles(): void
    {
        $source = $this->activeVisualSource();

        foreach ([
            'account-page',
            'apimanagement-page',
            'audit-page',
            'audit-show-page',
            'automation-page',
            'catalogs-page',
            'configuration-appearance-page',
            'configuration-health-console',
            'devtools-page',
            'documents-page',
            'localization-page',
            'media-page',
            'module-designer-page',
            'operations-page',
            'settings-console',
            'test-features-page',
            'users-enroll-page',
            'users-page',
            'home-surface',
            'landing-surface',
            'store-surface',
            'dashboard-surface',
            'catalyst-demo-surface',
            'rbac-section',
            'rbac-empty-state',
            'operations-hero',
            'operations-metric',
            'media-hero',
            'surface-executive',
        ] as $profile) {
            Assert::false(str_contains($source, $profile), "Active surface source still contains {$profile}.");
        }
    }

    public function testDevToolsDoesNotExposeTheLegacyTestLayoutAlias(): void
    {
        foreach ([
            'Repository/Framework/DevTools/routes.php',
            'app/Framework/Module/ModuleRegistry.php',
        ] as $path) {
            Assert::false(
                str_contains($this->read($path), '/test-layout'),
                "{$path} still exposes the legacy /test-layout alias."
            );
        }

        Assert::false(is_file($this->path('Repository/Framework/DevTools/Views/partials/uml/_header.phtml')));
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

    private function activeVisualSource(): string
    {
        $source = '';

        foreach ([
            'Repository/App',
            'Repository/Framework',
            'boot-core/template',
            'public/assets/css/catalyst',
            'public/assets/css/work',
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
                if (
                    str_contains($path, '/DemoUi/')
                    || str_contains($path, '/template-backup-')
                    || str_ends_with($path, '/public/assets/css/catalyst/inspinia-shell.css')
                ) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                if (!is_string($contents)) {
                    continue;
                }

                if ($file->getExtension() === 'css') {
                    $source .= "\n" . $contents;
                    continue;
                }

                if ($file->getExtension() !== 'phtml') {
                    continue;
                }

                if (preg_match_all('/class="([^"]+)"/', $contents, $matches) === false) {
                    continue;
                }

                $source .= "\n" . implode("\n", $matches[1]);
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
