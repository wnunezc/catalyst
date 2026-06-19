<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class Roadmap4OrganizationHierarchyOwnershipTest extends TestCase
{
    public function testOrganizationHierarchyBelongsOnlyToUsers(): void
    {
        $users = $this->read('Repository/Framework/Users/routes.php');

        Assert::contains('Catalyst\\Repository\\Users\\Controllers\\OrganizationHierarchyController', $users);
        Assert::same(9, substr_count($users, "'/users/organization-hierarchy"));
        Assert::same(4, substr_count($users, "OrganizationHierarchyController::class, 'store"));
        Assert::same(4, substr_count($users, "OrganizationHierarchyController::class, 'destroy"));
        Assert::true(is_file($this->path('Repository/Framework/Users/Controllers/OrganizationHierarchyController.php')));
        Assert::false(is_dir($this->path('Repository/Framework/Roles')));
    }

    private function path(string $relative): string
    {
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relative);
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
