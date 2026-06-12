<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class DataGridArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testDataGridLivesInANeutralFrameworkNamespace(): void
    {
        Assert::true(is_file($this->path('app/Framework/DataGrid/DataGrid.php')));
        Assert::true(is_file($this->path('app/Framework/DataGrid/DataGridViewModel.php')));
        Assert::same([], glob($this->path('app/Framework/Admin/Grid/*.php')) ?: []);

        $facade = $this->read('app/Framework/DataGrid/DataGrid.php');
        $viewModel = $this->read('app/Framework/DataGrid/DataGridViewModel.php');

        Assert::contains('namespace Catalyst\Framework\DataGrid;', $facade);
        Assert::contains('namespace Catalyst\Framework\DataGrid;', $viewModel);
        Assert::false(str_contains($facade, 'Framework\Admin'));
        Assert::false(str_contains($viewModel, 'Framework\Admin'));
    }

    public function testDataGridTemplateAndScopeExposeOnlyNeutralContracts(): void
    {
        $template = $this->read('boot-core/template/components/_datagrid.phtml');
        $scope = $this->read('boot-core/template/scope/components/_datagrid.php');
        $head = $this->read('boot-core/template/_head-assets.phtml');
        $documentScope = $this->read('app/Framework/View/DocumentScope.php');
        $styles = $this->read('public/assets/css/catalyst/datagrid.css');

        Assert::contains('data-datagrid="1"', $template);
        Assert::contains('datagrid-table', $template);
        Assert::contains('{{> "./_datagrid-cell" }}', $template);
        Assert::contains('DataGridViewModel::build($scope)', $scope);
        Assert::contains('href="{{ datagrid_asset_url }}"', $head);
        Assert::contains("AssetUrl::versioned('/assets/css/catalyst/datagrid.css')", $documentScope);
        Assert::contains('.datagrid-card', $styles);
        Assert::false(str_contains($template, 'admin-grid'));
        Assert::false(str_contains($template, 'admin-datagrid'));
        Assert::false(str_contains($template, 'data-admin-'));
    }

    public function testDataGridInteractionsAreOwnedByTheCentralRuntime(): void
    {
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');
        $interactions = $this->read('public/assets/js/catalyst/datagrid/interactions.js');

        Assert::contains("'datagrid.interactions'", $runtime);
        Assert::contains('../datagrid/interactions.js', $runtime);
        Assert::contains('[data-datagrid]', $runtime);
        Assert::contains('export function initDataGridInteractions', $interactions);
        Assert::contains('[data-grid-select-all]', $interactions);
        Assert::contains('[data-grid-per-page]', $interactions);
        Assert::contains('[data-grid-print]', $interactions);
        Assert::false(str_contains($interactions, 'DOMContentLoaded'));
    }

    public function testActiveConsumersDoNotUseTheReplacedAdminContract(): void
    {
        $roots = [
            $this->path('app'),
            $this->path('Repository'),
            $this->path('boot-core/template'),
            $this->path('test/framework/UnitTest'),
        ];

        foreach ($roots as $root) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }

                if ($file->getPathname() === __FILE__) {
                    continue;
                }

                $source = file_get_contents($file->getPathname());
                if (!is_string($source)) {
                    continue;
                }

                Assert::false(
                    str_contains($source, 'Catalyst\Framework\Admin\Grid'),
                    "{$file->getPathname()} still uses the Admin DataGrid namespace."
                );
                Assert::false(
                    str_contains($source, '_admin-datagrid'),
                    "{$file->getPathname()} still uses the replaced DataGrid partial."
                );
            }
        }
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function read(string $path): string
    {
        $source = file_get_contents($this->path($path));
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
