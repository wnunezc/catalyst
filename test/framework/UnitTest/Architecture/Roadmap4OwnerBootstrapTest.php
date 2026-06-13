<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class Roadmap4OwnerBootstrapTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testUsersAndAccountOwnersAreRegisteredWithCanonicalRoutes(): void
    {
        $plugin = $this->read('boot-core/plugins/framework.core.php');

        foreach (['Users', 'Account'] as $owner) {
            Assert::true(is_file($this->path("Repository/Framework/{$owner}/module.php")));
            Assert::true(is_file($this->path("Repository/Framework/{$owner}/routes.php")));
            $module = $this->read("Repository/Framework/{$owner}/module.php");
            $routes = $this->read("Repository/Framework/{$owner}/routes.php");
            $key = 'framework.' . strtolower($owner);

            Assert::contains("'{$key}'", $plugin);
            Assert::contains("'api' => []", $module);
            Assert::true($this->routeCount($routes) > 0);
        }
    }

    public function testLegacyOwnersAreRemovedAfterTheirVerticalMigrations(): void
    {
        Assert::false(is_dir($this->path('Repository/Framework/Roles')));
        Assert::false(is_dir($this->path('Repository/App/Surface/Account')));
        Assert::same(31, $this->routeCount(
            $this->read('Repository/Framework/Users/routes.php')
        ));
        Assert::same(19, $this->routeCount(
            $this->read('Repository/Framework/Account/routes.php')
        ));
    }

    private function routeCount(string $source): int
    {
        preg_match_all(
            "/\\\$router->(?:get|post)\\('([^']+)'/",
            $source,
            $matches
        );

        return count($matches[0]);
    }

    private function path(string $relative): string
    {
        return $this->root . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    private function read(string $relative): string
    {
        $path = $this->path($relative);
        $source = file_get_contents($path);
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
