<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class FrameworkConsumersArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testCrudOrientedFrameworkConsumersUseNeutralModuleWrappers(): void
    {
        $contracts = [
            'ApiPlatform' => ['apiplatform-admin-page', 'apiplatform-page'],
            'Automation' => ['automation-admin-page', 'automation-page'],
            'Catalogs' => ['catalogs-admin-page', 'catalogs-page'],
            'Documents' => ['documents-admin-page', 'documents-page'],
            'Media' => ['media-admin-page', 'media-page'],
        ];

        foreach ($contracts as $module => [$legacy, $neutral]) {
            $source = $this->moduleSource($module);
            Assert::contains($neutral, $source);
            Assert::false(
                str_contains($source, $legacy),
                "{$module} still uses its legacy admin wrapper."
            );
        }
    }

    public function testRemainingFrameworkConsumersUseNeutralSurfaceContracts(): void
    {
        $audit = $this->moduleSource('Audit');
        $roles = $this->moduleSource('Roles');
        $configuration = $this->moduleSource('Configuration');

        Assert::contains('surface-section-card', $audit);
        Assert::false(str_contains($audit, 'admin-section-card'));

        Assert::false(is_dir($this->path('Repository/Framework/Operations')));

        Assert::contains('surface-enrollment-form', $roles);
        Assert::false(str_contains($roles, 'rbac-admin-page'));
        Assert::false(str_contains($roles, 'admin-enrollment-form'));
        Assert::false(str_contains($roles, 'admin-section-card'));

        Assert::contains('surface-content-shell', $configuration);
        Assert::contains('surface-panel-grid', $configuration);
        Assert::false(str_contains($configuration, 'admin-content-shell'));
        Assert::false(str_contains($configuration, 'admin-section-card'));
        Assert::false(str_contains($configuration, 'admin-panel-'));
        Assert::false(str_contains($configuration, 'admin-list-'));
        Assert::false(str_contains($configuration, 'demo-ui-shell-body'));
    }

    public function testDemoUiUsesTheCommonShellWithoutOwningShellGeometry(): void
    {
        $demoUi = $this->moduleSource('DemoUi');
        $demoUiCss = $this->readFile('Repository/Framework/DemoUi/front/style.css');
        $globalUiCss = $this->readFile('public/assets/css/catalyst/ui-reference.css');
        $shell = $this->readFile('boot-core/template/shell.phtml');
        $topbar = $this->readFile('boot-core/template/_topbar.phtml');

        Assert::contains("'surface_context' => 'demo-ui'", $demoUi);
        Assert::false(str_contains($demoUi, 'demo-ui-shell-body'));
        Assert::false(str_contains($demoUi, 'demo-ui-page'));
        Assert::false(str_contains($demoUiCss, '.demo-ui-page'));
        Assert::false(str_contains($globalUiCss, '.demo'));
        Assert::false(str_contains($shell, 'demo-ui-'));
        Assert::false(str_contains($topbar, 'demo-ui-user-dropdown'));
        Assert::false(str_contains($topbar, 'demo-ui-account-label'));
        Assert::false(str_contains($topbar, 'demo-ui-account-summary'));
    }

    public function testDemoUiControllerOnlySuppliesNavigationCatalogData(): void
    {
        $controller = $this->readFile('Repository/Framework/DemoUi/Controllers/DemoUiController.php');

        Assert::contains("'navigation_model' => 'demo-ui'", $controller);
        Assert::contains("'selected_file' => \$selectedFile", $controller);
        Assert::false(str_contains($controller, 'buildNavGroups('));
        Assert::false(str_contains($controller, 'is_nested_collapse'));
        Assert::false(str_contains($controller, 'framework-configuracion'));
        Assert::false(str_contains($controller, 'Framework Configuration'));
        Assert::false(str_contains($controller, 'Framework Operations'));
    }

    private function moduleSource(string $module): string
    {
        $directory = $this->path("Repository/Framework/{$module}");
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );
        $source = '';

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile()) {
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

        return $source;
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function readFile(string $path): string
    {
        $source = file_get_contents($this->path($path));
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
