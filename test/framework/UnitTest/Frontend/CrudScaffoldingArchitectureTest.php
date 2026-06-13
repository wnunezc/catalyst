<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class CrudScaffoldingArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testCrudScaffoldingLivesInANeutralFrameworkNamespace(): void
    {
        Assert::true(is_file($this->path('app/Framework/Scaffolding/Crud/CrudScaffoldService.php')));
        Assert::same([], glob($this->path('app/Framework/Admin/Crud/*.php')) ?: []);

        foreach (glob($this->path('app/Framework/Scaffolding/Crud/*.php')) ?: [] as $file) {
            $source = file_get_contents($file);
            Assert::true(is_string($source));
            Assert::contains('namespace Catalyst\Framework\Scaffolding\Crud;', $source);
            Assert::false(str_contains($source, 'Framework\Admin'));
        }
    }

    public function testMakeCrudUsesTheNeutralScaffoldingCapability(): void
    {
        $command = $this->read('app/Framework/Cli/Commands/MakeCrudCommand.php');

        Assert::contains(
            'use Catalyst\Framework\Scaffolding\Crud\CrudScaffoldService;',
            $command
        );
        Assert::contains('Scaffold a CRUD module', $command);
        Assert::false(str_contains($command, 'privileged CRUD'));
        Assert::false(str_contains($command, 'generated admin module'));
    }

    public function testGeneratedCrudReusesGlobalCapabilitiesAndKeepsAuthorizationSeparate(): void
    {
        $controller = $this->read('app/Framework/Cli/Stubs/crud-controller.php.stub');
        $routes = $this->read('app/Framework/Cli/Stubs/crud-routes.php.stub');
        $index = $this->read('app/Framework/Cli/Stubs/crud-index-view.php.stub');
        $form = $this->read('app/Framework/Cli/Stubs/crud-form-view.php.stub');

        Assert::contains('use Catalyst\Framework\DataGrid\DataGrid;', $controller);
        Assert::contains('use Catalyst\Framework\Form\FormBuilder;', $controller);
        Assert::contains('{{> "components._datagrid" }}', $index);
        Assert::contains('{{> "components._form-builder" }}', $form);
        Assert::contains('new RoleMiddleware(permissions: {{PermissionLiteral}})', $routes);
        Assert::contains("authorizeResource('view-any'", $controller);
        Assert::false(str_contains($index, 'Privileged CRUD'));
        Assert::false(str_contains($controller, 'Generated privileged'));
        Assert::false(str_contains($form, 'privileged data'));
    }

    public function testCrudScaffoldCanBePreviewedWithoutWritingFiles(): void
    {
        $service = $this->read('app/Framework/Scaffolding/Crud/CrudScaffoldService.php');

        Assert::contains('public function preview(array $input): array', $service);
        Assert::contains('return $this->blueprintFactory()->build($input);', $service);
        Assert::contains('$blueprint = $this->preview($input);', $service);
    }

    public function testCrudScaffoldSmokeCoversTheRepresentativeFixture(): void
    {
        $smoke = $this->read('app/Framework/Cli/Commands/CrudScaffoldSmokeCommand.php');

        Assert::contains("return 'scaffold:crud-smoke';", $smoke);
        Assert::contains("'surface' => 'workspace'", $smoke);
        Assert::contains('Catalyst\\\\Framework\\\\DataGrid\\\\DataGrid', $smoke);
        Assert::contains('Catalyst\\\\Framework\\\\Form\\\\FormBuilder', $smoke);
        Assert::contains("'unsupported-surface-rejected'", $smoke);
        Assert::contains('->preview(', $smoke);
        Assert::false(str_contains($smoke, '->create('));
    }

    public function testActiveSourcesDoNotReferenceTheReplacedAdminCrudNamespace(): void
    {
        foreach (['app', 'Repository', 'boot-core', 'test/framework/UnitTest'] as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->path($directory),
                    \FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getPathname() === __FILE__) {
                    continue;
                }

                $source = file_get_contents($file->getPathname());
                if (!is_string($source)) {
                    continue;
                }

                Assert::false(
                    str_contains($source, 'Catalyst\Framework\Admin\Crud'),
                    "{$file->getPathname()} still uses the Admin CRUD namespace."
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
