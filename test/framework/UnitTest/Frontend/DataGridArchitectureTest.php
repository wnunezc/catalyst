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
        $viewModel = $this->read('app/Framework/DataGrid/DataGridViewModel.php');

        Assert::contains('data-datagrid="1"', $template);
        Assert::contains('class="{{ grid_table_class }}"', $template);
        Assert::contains("'datagrid-table'", $viewModel);
        Assert::contains('{{> "./_datagrid-cell" }}', $template);
        Assert::contains('DataGridViewModel::build($scope)', $scope);
        Assert::contains('href="{{ datagrid_asset_url }}"', $head);
        Assert::contains("AssetUrl::versioned('/assets/css/catalyst/datagrid.css')", $documentScope);
        Assert::contains('[data-datagrid]', $styles);
        Assert::false(str_contains($styles, '.datagrid-card'));
        Assert::false(str_contains($template, 'admin-grid'));
        Assert::false(str_contains($template, 'admin-datagrid'));
        Assert::false(str_contains($template, 'data-admin-'));
    }

    public function testDataGridUsesResponsiveInspiniaTableCompositionWithoutForcedNoWrap(): void
    {
        $template = $this->read('boot-core/template/components/_datagrid.phtml');

        Assert::contains('<div class="table-responsive {{ grid_table_scroll_class }}"', $template);
        Assert::contains('<div class="{{ grid_table_body_class }}">', $template);
        Assert::contains('class="{{ grid_table_class }}"', $template);
        Assert::contains("['table', 'table-striped', 'table-hover', 'dt-responsive', 'align-middle', 'mb-0', 'datagrid-table']", $this->read('app/Framework/DataGrid/DataGridViewModel.php'));
        Assert::false(
            str_contains($template, 'table-nowrap'),
            'DataGrid must allow long audit and metadata cells to wrap inside the responsive table container.'
        );
    }

    public function testDataGridRendersOneSharedInsetToolbarAboveAndBelowTheTable(): void
    {
        $template = $this->read('boot-core/template/components/_datagrid.phtml');
        $toolbar = $this->read('boot-core/template/components/_datagrid-toolbar.phtml');

        Assert::same(2, substr_count($template, '{{> "./_datagrid-toolbar" }}'));
        Assert::same(2, substr_count($template, 'class="card-body py-0"'));
        Assert::contains('class="border-bottom py-2"', $template);
        Assert::contains('class="border-top py-2"', $template);
        Assert::false(str_contains($template, '<div class="card-footer py-2">'));

        Assert::contains('grid_has_tools', $toolbar);
        Assert::contains('data-grid-per-page', $toolbar);
        Assert::contains('grid_pagination_summary', $toolbar);
        Assert::contains('grid_first_url', $toolbar);
        Assert::contains('grid_prev_url', $toolbar);
        Assert::contains('grid_next_url', $toolbar);
        Assert::contains('grid_last_url', $toolbar);
        Assert::false(str_contains($toolbar, 'id="grid-per-page-'));
    }

    public function testFlushTableCompositionIsSelectedByTheGlobalDataGridContract(): void
    {
        $flushTableTemplates = [];

        foreach ([
            $this->path('Repository/App'),
            $this->path('Repository/Framework'),
            $this->path('boot-core/template'),
        ] as $root) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'phtml') {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                if (
                    str_contains($path, '/DemoUi/')
                    || str_contains($path, '/template-backup-')
                    || str_contains($path, '/exports/')
                ) {
                    continue;
                }

                $source = file_get_contents($file->getPathname());
                if (
                    is_string($source)
                    && str_contains($source, '<table')
                    && str_contains($source, 'card-body p-0')
                ) {
                    $flushTableTemplates[] = $path;
                }
            }
        }

        Assert::same(
            [],
            $flushTableTemplates
        );

        $builder = $this->read('app/Framework/DataGrid/DataGrid.php');
        $viewModel = $this->read('app/Framework/DataGrid/DataGridViewModel.php');
        $template = $this->read('boot-core/template/components/_datagrid.phtml');

        Assert::contains('public function insetTable(bool $inset = true): self', $builder);
        Assert::contains("'inset_table' => true", $builder);
        Assert::contains("'grid_table_body_class' => !empty(\$grid['inset_table']) ? 'card-body' : 'card-body p-0'", $viewModel);
        Assert::contains('<div class="{{ grid_table_body_class }}">', $template);
        Assert::false(str_contains($template, '<div class="card-body p-0">'));
    }

    public function testClassicTablesUsePaddedCardBodies(): void
    {
        foreach ([
            'Repository/Framework/Workspaces/Views/pages/localization/index.phtml',
            'Repository/Framework/Workspaces/Views/pages/module-designer/index.phtml',
        ] as $path) {
            $source = $this->read($path);

            Assert::contains('<div class="card-body">', $source);
            Assert::contains('<div class="table-responsive">', $source);
            Assert::false(str_contains($source, 'card-body p-0'));
        }
    }

    public function testActiveHtmlTablesUseBootstrapResponsiveContainers(): void
    {
        foreach ([
            $this->path('Repository/App'),
            $this->path('Repository/Framework'),
            $this->path('boot-core/template'),
        ] as $root) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'phtml') {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                if (
                    str_contains($path, '/DemoUi/')
                    || str_contains($path, '/template-backup-')
                    || str_contains($path, '/exports/')
                ) {
                    continue;
                }

                $source = file_get_contents($file->getPathname());
                if (!is_string($source)) {
                    continue;
                }

                $tableCount = substr_count($source, '<table');
                if ($tableCount === 0) {
                    continue;
                }

                Assert::same(
                    $tableCount,
                    substr_count($source, 'class="table-responsive'),
                    "{$path} must provide one Bootstrap responsive container per active HTML table."
                );
            }
        }
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

    public function testDataGridLongTextAffordancesAreGlobalAndAutomatic(): void
    {
        $columnNormalizer = $this->read('app/Framework/DataGrid/DataGridColumnNormalizer.php');
        $rowNormalizer = $this->read('app/Framework/DataGrid/DataGridRowNormalizer.php');
        $viewModel = $this->read('app/Framework/DataGrid/DataGridViewModel.php');
        $cellTemplate = $this->read('boot-core/template/components/_datagrid-cell.phtml');
        $interactions = $this->read('public/assets/js/catalyst/datagrid/interactions.js');
        $styles = $this->read('public/assets/css/catalyst/datagrid.css');

        Assert::contains("'truncate' => self::normalizeTruncateConfig(\$column['truncate'] ?? null)", $columnNormalizer);
        Assert::contains('DataGridColumnNormalizer::normalizeTruncateConfig', $rowNormalizer);
        Assert::contains("'threshold' => 35", $columnNormalizer);
        Assert::contains('$automatic = $config === null', $columnNormalizer);
        Assert::contains("\$threshold = (int) (\$truncate['threshold'] ?? 35)", $viewModel);
        Assert::contains('mb_strlen($fullText) > $threshold', $viewModel);
        Assert::contains("'has_truncated_text'", $viewModel);
        Assert::contains("'copyable'", $viewModel);
        Assert::contains("'primary_truncated'", $viewModel);
        Assert::contains("'secondary_truncated'", $viewModel);
        Assert::contains("'primary_display_text'", $viewModel);
        Assert::contains("'secondary_display_text'", $viewModel);
        Assert::contains("'display_text'", $viewModel);
        Assert::contains("mb_substr(\$text, 0, \$threshold) . '...'", $viewModel);
        Assert::contains('$visibleColumnCount', $viewModel);
        Assert::contains('max(15, 35 - (max(0, $visibleColumnCount - 6) * 5))', $viewModel);
        Assert::contains("'grid_visible_column_count'", $viewModel);
        Assert::contains("'grid_visible_character_limit'", $viewModel);
        Assert::contains("'grid_table_scroll_class'", $viewModel);
        Assert::contains("'grid_table_class'", $viewModel);
        Assert::contains("'primary_copyable'", $viewModel);
        Assert::contains("'secondary_copyable'", $viewModel);
        Assert::contains('data-grid-stack-line="primary"', $cellTemplate);
        Assert::contains('data-grid-stack-line="secondary"', $cellTemplate);
        Assert::contains('data-bs-toggle="tooltip"', $cellTemplate);
        Assert::contains('data-grid-copy', $cellTemplate);
        Assert::contains('data-grid-copy-value="{{ full_text }}"', $cellTemplate);
        Assert::contains('[data-grid-copy]', $interactions);
        Assert::contains('navigator.clipboard.writeText', $interactions);
        Assert::contains("document.execCommand('copy')", $interactions);
        Assert::contains('copyWithFallback', $interactions);
        Assert::contains("AssetUrl::versionedTree(\n                '/assets/js/catalyst/runtime/ui-runtime.js',\n                '/assets/js/catalyst'\n            )", $this->read('app/Framework/View/DocumentScope.php'));
        Assert::contains('.datagrid-cell-text', $styles);
        Assert::contains('text-overflow: ellipsis', $styles);
        Assert::contains('.datagrid-cell-text--sm', $styles);
        Assert::contains('.datagrid-cell-text--md', $styles);
        Assert::contains('.datagrid-cell-text--lg', $styles);
        Assert::contains('.datagrid-table-scroll--wide', $styles);
        Assert::contains('.datagrid-table-scroll--vertical', $styles);
        Assert::contains('position: sticky', $styles);
        Assert::false(str_contains($cellTemplate, 'onclick='));

        $users = $this->read('Repository/Framework/Users/Controllers/UserManagementController.php');
        Assert::false(
            str_contains($users, "'truncate' =>"),
            'Consumers must not activate the global long-text behavior during view construction.'
        );
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
