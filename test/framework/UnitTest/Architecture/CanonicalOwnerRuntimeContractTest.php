<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Framework\View\ModuleViewPathRegistrar;
use Catalyst\Framework\View\View;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class CanonicalOwnerRuntimeContractTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testReconstructedWorkspacesViewsUseResolvableModulePagePaths(): void
    {
        foreach (['module-designer', 'localization'] as $surface) {
            Assert::true(is_file($this->path("Repository/Framework/Workspaces/Views/pages/{$surface}/index.phtml")));
            Assert::true(is_file($this->path("Repository/Framework/Workspaces/Views/scope/pages/{$surface}/index.php")));
            Assert::false(is_file($this->path("Repository/Framework/Workspaces/Views/{$surface}/index.phtml")));
        }

        ModuleRegistry::getInstance()->flushCache();
        $view = View::getInstance();
        (new ModuleViewPathRegistrar())->register($view, ModuleRegistry::getInstance()->all());

        Assert::true($view->exists('workspaces.module-designer.index'));
        Assert::true($view->exists('workspaces.localization.index'));
    }

    public function testMigratedAuthorizationConsumersUseCanonicalResources(): void
    {
        $contracts = [
            'workspaces-catalogs' => [
                'Repository/Framework/Workspaces/Catalogs/Controllers/CatalogController.php',
                'Repository/Framework/Workspaces/Catalogs/Requests/CatalogDefinitionRequest.php',
                'Repository/Framework/Workspaces/Catalogs/Requests/CatalogItemRequest.php',
            ],
            'workspaces-media-fields' => [
                'Repository/Framework/Workspaces/Media/Controllers/MetadataFieldController.php',
                'Repository/Framework/Workspaces/Media/Requests/MetadataFieldDefinitionRequest.php',
            ],
            'workspaces-media-library' => [
                'Repository/Framework/Workspaces/Media/Controllers/MediaLibraryController.php',
                'Repository/Framework/Workspaces/Media/Requests/MediaItemRequest.php',
            ],
            'workspaces-document-templates' => [
                'Repository/Framework/Workspaces/Documents/Controllers/DocumentTemplateController.php',
                'Repository/Framework/Workspaces/Documents/Controllers/DocumentTemplateApiController.php',
                'Repository/Framework/Workspaces/Documents/Requests/DocumentTemplateRequest.php',
            ],
            'operations-api-management' => [
                'Repository/Framework/Operations/ApiManagement/Controllers/ApiManagementController.php',
                'Repository/Framework/Operations/ApiManagement/Requests/ApiTokenRequest.php',
            ],
            'operations-automation-rules' => [
                'Repository/Framework/Operations/Automation/Controllers/AutomationRuleController.php',
                'Repository/Framework/Operations/Automation/Controllers/AutomationRuleApiController.php',
                'Repository/Framework/Operations/Automation/Requests/AutomationRuleRequest.php',
            ],
        ];

        foreach ($contracts as $resource => $files) {
            foreach ($files as $file) {
                Assert::contains("'{$resource}'", $this->read($file), "{$file} does not use {$resource}.");
            }
        }
    }

    public function testPublicWorkflowAndVersionResourceKeysRemainStable(): void
    {
        $workflow = $this->read('Repository/Framework/Api/Controllers/WorkflowApiController.php');
        $version = $this->read('Repository/Framework/Api/Controllers/VersionApiController.php');

        foreach (['document-templates', 'automation-rules'] as $resourceKey) {
            Assert::contains("'{$resourceKey}'", $workflow);
            Assert::contains("'{$resourceKey}'", $version);
        }
    }

    private function path(string $relative): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    private function read(string $relative): string
    {
        $source = file_get_contents($this->path($relative));
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$relative}.");
        }

        return $source;
    }
}
