<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ModuleDesignerOwnershipMigrationTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testModuleDesignerIsOwnedByWorkspacesWithFourSafeRoutes(): void
    {
        $routes = $this->read('Repository/Framework/Workspaces/routes.php');

        Assert::true(is_dir($this->root . '/Repository/Framework/Workspaces/ModuleDesigner'));
        Assert::same(4, $this->routeCount($routes, '/workspaces/module-designer'));
        Assert::contains(
            "get('/workspaces/module-designer', [ModuleDesignerController::class, 'index'])",
            $routes
        );
        Assert::contains(
            "post('/workspaces/module-designer/preview', [ModuleDesignerController::class, 'preview'])",
            $routes
        );
        Assert::contains(
            "post('/workspaces/module-designer/generate', [ModuleDesignerController::class, 'generate'])",
            $routes
        );
        Assert::contains(
            "post('/workspaces/module-designer/modules/{key}/delete', [ModuleDesignerController::class, 'destroy'])",
            $routes
        );
        Assert::false(str_contains($routes, "get('/workspaces/module-designer/preview'"));
        Assert::false(str_contains($routes, "get('/workspaces/module-designer/generate'"));
        Assert::contains('WorkspacesAccessContract::MODULE_DESIGNER', $routes);
    }

    public function testModuleDesignerUsesCurrentScaffoldingAndNoLegacyRuntimeState(): void
    {
        $controller = $this->read(
            'Repository/Framework/Workspaces/ModuleDesigner/Controllers/ModuleDesignerController.php'
        );
        $request = $this->read(
            'Repository/Framework/Workspaces/ModuleDesigner/Requests/ModuleDesignerRequest.php'
        );
        $manifest = $this->read('Repository/Framework/Workspaces/module.php');

        Assert::contains(
            'namespace Catalyst\\Repository\\Workspaces\\ModuleDesigner\\Controllers;',
            $controller
        );
        Assert::contains('ModuleScaffoldService', $controller);
        Assert::contains('ModuleInspector', $controller);
        Assert::contains('ModuleLinter', $controller);
        Assert::false(str_contains($controller, '_operations_'));
        Assert::false(str_contains($controller, "'admin'"));
        Assert::false(str_contains($controller, 'legacy'));
        Assert::contains('extends FormRequest', $request);
        Assert::contains('manage-workspaces-module-designer', $manifest);
        Assert::contains("'/workspaces/module-designer'", $manifest);
    }

    private function routeCount(string $source, string $prefix): int
    {
        preg_match_all(
            "/\\\$router->(?:get|post)\\('" . preg_quote($prefix, '/') . "(?:'|\\/)/",
            $source,
            $matches
        );

        return count($matches[0]);
    }

    private function read(string $relativePath): string
    {
        $path = $this->root . '/' . $relativePath;
        $source = is_file($path) ? file_get_contents($path) : false;

        return is_string($source) ? $source : '';
    }
}
