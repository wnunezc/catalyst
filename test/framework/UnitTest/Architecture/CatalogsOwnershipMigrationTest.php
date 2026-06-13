<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class CatalogsOwnershipMigrationTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testCatalogsIsOwnedOnlyByWorkspaces(): void
    {
        Assert::false(is_dir($this->root . '/Repository/Framework/Catalogs'));
        Assert::true(is_dir($this->root . '/Repository/Framework/Workspaces/Catalogs'));

        $routes = (string) file_get_contents($this->root . '/Repository/Framework/Workspaces/routes.php');
        preg_match_all(
            "/\\\$router->(?:get|post)\\('\\/workspaces\\/catalogs(?:'|\\/)/",
            $routes,
            $matches
        );

        Assert::same(14, count($matches[0]));
        Assert::contains(
            'Catalyst\\Repository\\Workspaces\\Catalogs\\Controllers\\CatalogController',
            $routes
        );
        Assert::contains('WorkspacesAccessContract::CATALOGS', $routes);
        Assert::false(str_contains($routes, 'manage-catalogs'));
    }

    public function testCatalogsNamespaceViewsNavigationAndAssetsFollowTheOwner(): void
    {
        $controller = (string) file_get_contents(
            $this->root . '/Repository/Framework/Workspaces/Catalogs/Controllers/CatalogController.php'
        );
        $manifest = (string) file_get_contents($this->root . '/Repository/Framework/Workspaces/module.php');
        $frontRuntime = (string) file_get_contents(
            $this->root . '/Repository/Framework/Workspaces/front/script.js'
        );

        Assert::contains('namespace Catalyst\\Repository\\Workspaces\\Catalogs\\Controllers;', $controller);
        Assert::contains("'href' => '/workspaces/catalogs'", $manifest);
        Assert::contains(
            "['permissions_any' => ['manage-workspaces-catalogs']]",
            $manifest
        );
        Assert::contains("'resource' => 'workspaces-catalogs'", $manifest);
        Assert::contains("name: 'workspaces.catalogs.code-wrap'", $frontRuntime);
    }
}
