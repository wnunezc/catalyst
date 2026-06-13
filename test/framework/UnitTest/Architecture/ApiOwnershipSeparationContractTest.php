<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ApiOwnershipSeparationContractTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testTransversalApiHasIndependentFrameworkOwner(): void
    {
        Assert::true(is_file($this->path('Repository/Framework/Api/module.php')));
        Assert::true(is_file($this->path('Repository/Framework/Api/routes.php')));

        $routes = $this->read('Repository/Framework/Api/routes.php');
        foreach ([
            '/api/v1/catalog',
            '/api/v1/calendar/events',
            '/api/v1/workflows',
            '/api/v1/workflows/{id}/transition',
            '/api/v1/versions/{resourceKey}/{recordId}',
            '/api/v1/versions/{id}/restore',
        ] as $route) {
            Assert::contains("'{$route}'", $routes);
            Assert::false(str_contains($this->read('Repository/Framework/Operations/routes.php'), "'{$route}'"));
        }
    }

    public function testApiManagementRemainsAnOperationsAdministrativeSurface(): void
    {
        $operationsRoutes = $this->read('Repository/Framework/Operations/routes.php');
        $operationsModule = $this->read('Repository/Framework/Operations/module.php');

        Assert::contains('/operations/api-management', $operationsRoutes);
        Assert::contains('Operations\\ApiManagement', $operationsRoutes);
        Assert::contains('manage-operations-api-management', $operationsModule);
        Assert::false(str_contains($operationsRoutes, 'ApiPlatform'));
        Assert::false(str_contains($operationsModule, '/operations/api-platform'));
    }

    public function testApiPlatformNamespaceAndDirectoryAreRetired(): void
    {
        Assert::false(is_dir($this->path('Repository/Framework/Operations/ApiPlatform')));
        Assert::true(is_dir($this->path('Repository/Framework/Operations/ApiManagement')));

        foreach ([
            'Repository/Framework/Api/Controllers/CatalogApiController.php',
            'Repository/Framework/Api/Controllers/CalendarApiController.php',
            'Repository/Framework/Api/Controllers/WorkflowApiController.php',
            'Repository/Framework/Api/Controllers/VersionApiController.php',
        ] as $file) {
            Assert::true(is_file($this->path($file)), "{$file} is missing.");
            Assert::contains('namespace Catalyst\\Repository\\Api\\Controllers;', $this->read($file));
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
