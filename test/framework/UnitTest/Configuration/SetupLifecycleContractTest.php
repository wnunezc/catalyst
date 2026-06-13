<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class SetupLifecycleContractTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testOnlyCompletionControllerCanActivateConfiguredState(): void
    {
        $configuration = $this->tree('Repository/Framework/Configuration');
        $completion = $this->read('Repository/Framework/Configuration/Controllers/SetupCompletionController.php');

        Assert::same(1, substr_count($configuration, "\$appProject['project_config'] = true"));
        Assert::contains("\$appProject['project_config'] = true", $completion);
        Assert::contains("\$appProject['project_config'] = false", $completion);
        Assert::false(str_contains($completion, "\$request->input('account_name'"));
        Assert::false(str_contains($completion, "\$request->input('account_password'"));
    }

    public function testPartialApplicationWriterPreservesConfiguredState(): void
    {
        $writer = $this->read('Repository/Framework/Configuration/Support/AppConfigWriter.php');

        Assert::contains("'project_config' => (bool) (\$existing['project_config'] ?? false)", $writer);
        Assert::false(str_contains($writer, "'project_config' => true"));
    }

    public function testEverySetupMutationUsesGuardAndThrottle(): void
    {
        $routes = $this->read('Repository/Framework/Configuration/routes.php');

        Assert::same(17, substr_count($routes, '->throttle(ConfigurationAccessContract::SETUP_THROTTLE)'));
        Assert::same(18, substr_count($routes, '->middleware($setupMiddleware)'));
    }

    private function tree(string $relative): string
    {
        $contents = '';
        $directory = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $contents .= (string) file_get_contents($file->getPathname());
            }
        }

        return $contents;
    }

    private function read(string $relative): string
    {
        return (string) file_get_contents(
            $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)
        );
    }
}
