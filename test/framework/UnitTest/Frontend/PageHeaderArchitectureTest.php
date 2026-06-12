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

        Assert::contains('final class PageHeaderViewModel', $viewModel);
        Assert::contains('PageHeaderViewModel::build($scope)', $companion);
        Assert::contains('data-page-header', $template);
        Assert::contains('page-header__title', $template);
        Assert::contains('{{ page_header_title }}', $template);
        Assert::false(str_contains($template, '{{{ page_header_'));
        Assert::false(str_contains($viewModel, 'Admin'));
        Assert::false(str_contains($viewModel, 'Account'));
        Assert::false(str_contains($template, 'admin_'));
        Assert::false(str_contains($template, 'account-'));
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
        Assert::same('4', $scope['page_header_metrics'][0]['value']);
        Assert::true($scope['page_header_tabs'][0]['is_active']);
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
