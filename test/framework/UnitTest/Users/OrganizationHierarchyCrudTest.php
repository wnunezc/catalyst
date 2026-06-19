<?php

declare(strict_types=1);

namespace CatalystTest\Users;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class OrganizationHierarchyCrudTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testOrganizationHierarchyExposesDeleteRoutesAndDependencyLocks(): void
    {
        $repo = $this->read('app/Framework/Organization/OrganizationRepository.php');
        $controller = $this->read('Repository/Framework/Users/Controllers/OrganizationHierarchyController.php');
        $routes = $this->read('Repository/Framework/Users/routes.php');
        $template = $this->read('Repository/Framework/Users/Views/pages/organization-hierarchy.phtml');
        $scope = $this->read('Repository/Framework/Users/Views/scope/pages/organization-hierarchy.php');

        foreach (['deleteOrganization', 'deleteUnit', 'deleteScope', 'deleteLevel', 'dependencyCounts'] as $method) {
            Assert::contains($method, $repo);
        }

        foreach (['destroyOrganization', 'destroyUnit', 'destroyScope', 'destroyLevel'] as $method) {
            Assert::contains($method, $controller);
        }

        foreach (['organizations/{id}/delete', 'units/{id}/delete', 'scopes/{id}/delete', 'levels/{id}/delete'] as $route) {
            Assert::contains($route, $routes);
        }

        Assert::contains('delete_action', $scope);
        Assert::contains('lock_reason', $scope);
        Assert::contains('data-catalyst="form"', $template);
        Assert::contains('text-bg-secondary', $template);
        Assert::contains('org-update-', $template);
        Assert::contains('scope-update-', $template);
        Assert::contains('level-update-', $template);
        Assert::contains('unit-update-', $template);
    }

    private function read(string $path): string
    {
        $source = file_get_contents($this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
