<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use Catalyst\Framework\View\PageHeaderViewModel;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class PageHeaderArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testPageHeaderHasOneNeutralGlobalImplementation(): void
    {
        $viewModel = $this->read('app/Framework/View/PageHeaderViewModel.php');
        $template = $this->read('boot-core/template/components/_page-header.phtml');
        $companion = $this->read('boot-core/template/scope/components/_page-header.php');
        $surfaceCss = $this->read('public/assets/css/catalyst/surfaces.css');

        Assert::contains('final class PageHeaderViewModel', $viewModel);
        Assert::contains('PageHeaderViewModel::build($scope)', $companion);
        Assert::contains('data-page-header', $template);
        Assert::contains('class="page-title-head d-flex align-items-center', $template);
        Assert::contains('<h4 class="page-main-title m-0">', $template);
        Assert::false(str_contains($template, '<h1'));
        Assert::false(str_contains($template, 'page-title-head d-flex align-items-center page-header'));
        Assert::contains('align-items: center;', $surfaceCss);
        Assert::contains('flex: 0 0 1.5rem;', $surfaceCss);
        Assert::contains('font-size: 1.125rem;', $surfaceCss);
        Assert::contains('.page-header__help-trigger > i', $surfaceCss);
        Assert::contains('page-header-context', $template);
        Assert::contains('{{ page_header_title }}', $template);
        Assert::false(str_contains($template, 'page-header card'));
        Assert::false(str_contains($template, '{{{ page_header_'));
        Assert::false(str_contains($viewModel, 'Admin'));
        Assert::false(str_contains($viewModel, 'Account'));
        Assert::false(str_contains($template, 'admin_'));
        Assert::false(str_contains($template, 'account-'));
    }

    public function testGlobalPageHeaderConsumesRegistryBreadcrumbsWithNativeMarkup(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $content = $this->read('boot-core/template/_content.phtml');
        $header = $this->read('boot-core/template/components/_page-header.phtml');

        Assert::contains('NavigationRegistry::getInstance()->breadcrumbs', $scope);
        Assert::contains("'breadcrumb_items' => \$breadcrumbItems", $scope);
        Assert::contains("'has_breadcrumbs' => \$breadcrumbItems !== []", $scope);
        Assert::contains('class="breadcrumb m-0 py-0"', $header);
        Assert::contains('class="breadcrumb-item', $header);
        Assert::false(str_contains($content, 'class="breadcrumb'));
        Assert::false(str_contains($content, 'account-breadcrumb'));
    }

    public function testActiveViewsUseOnlyTheGlobalPageHeaderPartial(): void
    {
        foreach ($this->files(['Repository', 'boot-core/template'], ['php', 'phtml']) as $file) {
            if (str_contains($file, 'template-backup-')) {
                continue;
            }

            $source = file_get_contents($file);
            if (!is_string($source)) {
                throw new \RuntimeException("Unable to read {$file}.");
            }

            Assert::false(str_contains($source, 'components._admin-page-header'));
            Assert::false(str_contains($source, 'account-page-header'));
            Assert::false(str_contains($source, "'admin_header'"));
        }
    }

    public function testViewModelNormalizesActionsMetricsAndTabs(): void
    {
        $scope = PageHeaderViewModel::build([
            'page_header' => [
                'title' => '<unsafe>',
                'actions' => [
                    ['label' => 'Open', 'href' => '/open', 'class' => 'btn-link'],
                    ['label' => ''],
                ],
                'metrics' => [
                    ['label' => 'Total', 'value' => 4],
                ],
                'tabs' => [
                    ['label' => 'Overview', 'href' => '/overview', 'is_active' => true],
                ],
            ],
        ]);

        Assert::same('<unsafe>', $scope['page_header_title']);
        Assert::contains('btn-outline-secondary', $scope['page_header_actions'][0]['class']);
        Assert::same(1, count($scope['page_header_actions']));
        Assert::true($scope['page_header_has_aside']);
        Assert::same('4', $scope['page_header_metrics'][0]['value']);
        Assert::true($scope['page_header_tabs'][0]['is_active']);
        Assert::true($scope['page_header_has_context']);
    }

    public function testPageHeaderHelpMovesSupportingCopyIntoAnAccessibleModalAcrossCommonSurfaces(): void
    {
        $template = $this->read('boot-core/template/components/_page-header.phtml');
        $dashboard = $this->read('Repository/App/Surface/Dashboard/Controllers/DashboardController.php');
        $scope = PageHeaderViewModel::build([
            'page_header' => [
                'title' => 'Dashboard',
                'eyebrow' => 'Account center',
                'description' => 'Personal summary.',
            ],
        ]);
        $scopeWithoutDescription = PageHeaderViewModel::build([
            'page_header' => [
                'title' => 'Dashboard',
            ],
        ]);

        Assert::true($scope['page_header_has_help']);
        Assert::same('page-header-help', $scope['page_header_help_id']);
        Assert::same('Account center', $scope['page_header_help_eyebrow']);
        Assert::same('Personal summary.', $scope['page_header_help_description']);
        Assert::false($scopeWithoutDescription['page_header_has_help']);
        Assert::contains('data-page-header-help', $template);
        Assert::contains('data-bs-target="#{{ page_header_help_id }}"', $template);
        Assert::contains('id="{{ page_header_help_id }}"', $template);
        Assert::false(str_contains($dashboard, "'help' => true"));
        Assert::false(str_contains($dashboard, "'help_id' =>"));
    }

    public function testEveryCommonSurfacePageHeaderProducerProvidesHelpContent(): void
    {
        $producers = [];
        foreach ($this->files(['Repository'], ['php']) as $file) {
            $source = file_get_contents($file);
            if (!is_string($source) || !str_contains($source, "'page_header' =>")) {
                continue;
            }

            $producers[] = $file;
            Assert::contains("'description' =>", $source);
        }

        Assert::same(42, count($producers));
    }

    /**
     * @param list<string> $directories
     * @param list<string> $extensions
     * @return list<string>
     */
    private function files(array $directories, array $extensions): array
    {
        $files = [];
        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory),
                    \FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo
                    && $file->isFile()
                    && in_array($file->getExtension(), $extensions, true)
                ) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    private function read(string $path): string
    {
        $source = file_get_contents(
            $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
        );

        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
