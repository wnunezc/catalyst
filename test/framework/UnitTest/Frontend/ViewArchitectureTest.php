<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ViewArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testCompleteViewsAlwaysUseTheCanonicalDocument(): void
    {
        $view = $this->read('app/Framework/View/View.php');

        Assert::contains('DocumentScope::prepare(', $view);
        Assert::contains("['content' => TrustedHtml::fromString(\$content)]", $view);
        Assert::contains("[PD, 'boot-core', 'template', 'document']", $view);
        Assert::false(str_contains($view, 'documentProfiles'));
        Assert::false(str_contains($view, 'findLayout'));
    }

    public function testFragmentsHaveAnExplicitRenderingContract(): void
    {
        $view = $this->read('app/Framework/View/View.php');
        $controller = $this->read('app/Framework/Controllers/Controller.php');

        Assert::contains('public function renderFragment(', $view);
        Assert::contains('protected function viewFragment(', $controller);
        Assert::contains('return $this->viewEngine->renderFragment(', $controller);
    }

    public function testDocumentScopeDoesNotAcceptOrResolveProfiles(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains('public static function prepare(array $scope): array', $scope);
        Assert::false(str_contains($scope, '$profile'));
        Assert::false(str_contains($scope, 'documentProfiles'));
        Assert::false(str_contains($scope, "['admin', 'document'"));
    }

    public function testScaffoldingDoesNotGenerateLayoutArguments(): void
    {
        $blueprint = $this->read('app/Framework/Module/ModuleBlueprintFactory.php');
        $files = $this->read('app/Framework/Module/ModuleFileFactory.php');

        Assert::false(str_contains($blueprint, "'layout' =>"));
        Assert::false(str_contains($files, "\$blueprint['layout']"));
        Assert::false(str_contains($files, '200, '));
    }

    public function testActiveControllersDoNotSelectLegacyLayouts(): void
    {
        $patterns = [
            ", 200, 'admin'",
            ", 200, 'document'",
            ", 200, 'account'",
            ", 200, 'auth'",
            ", 200, 'base'",
            ", 200, 'public'",
            ", 200, 'blank'",
            ", 200, 'error'",
        ];

        foreach ($this->phpFiles(['app', 'Repository']) as $file) {
            $source = file_get_contents($file);
            if (!is_string($source)) {
                throw new \RuntimeException("Unable to read {$file}.");
            }

            foreach ($patterns as $pattern) {
                Assert::false(
                    str_contains($source, $pattern),
                    "Legacy layout selection remains in {$file}: {$pattern}"
                );
            }
        }
    }

    /**
     * @param list<string> $directories
     * @return list<string>
     */
    private function phpFiles(array $directories): array
    {
        $files = [];

        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->root . DIRECTORY_SEPARATOR . $directory,
                    \FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
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
