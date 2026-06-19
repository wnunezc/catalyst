<?php

declare(strict_types=1);

namespace CatalystTest\Module;

use Catalyst\Framework\Module\ModuleManagementService;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ModuleDesignerManagementTest extends TestCase
{
    public function testModuleDesignerExposesManagementAndSafeDeleteGuards(): void
    {
        $service = $this->source('app/Framework/Module/ModuleManagementService.php');
        $controller = $this->source('Repository/Framework/Workspaces/ModuleDesigner/Controllers/ModuleDesignerController.php');
        $routes = $this->source('Repository/Framework/Workspaces/routes.php');
        $view = $this->source('Repository/Framework/Workspaces/Views/pages/module-designer/index.phtml');

        Assert::true(class_exists(ModuleManagementService::class));
        Assert::contains('delete_allowed', $service);
        Assert::contains("'scope'] ?? '') !== 'App'", $service);
        Assert::contains('dependency_counts', $service);
        Assert::contains('function destroy', $controller);
        Assert::contains('/workspaces/module-designer/modules/{key}/delete', $routes);
        Assert::contains('data-module-designer-row', $view);
        Assert::contains('delete_block_reason', $view);
    }

    private function source(string $path): string
    {
        $root = defined('PD') ? \PD : getcwd();
        $contents = file_get_contents($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));

        return is_string($contents) ? $contents : '';
    }
}
