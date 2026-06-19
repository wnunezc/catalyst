<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class Roadmap4RbacOwnershipTest extends TestCase
{
    public function testRbacRoutesAndClassesBelongOnlyToUsers(): void
    {
        $users = $this->read('Repository/Framework/Users/routes.php');

        foreach (['RolesController', 'PermissionsController', 'UserRolesController'] as $controller) {
            Assert::contains("Catalyst\\Repository\\Users\\Controllers\\{$controller}", $users);
        }

        Assert::same(35, count($this->routeKeys($users)));
        Assert::same(22, substr_count($users, "->throttle('privileged_mutation')"));
        Assert::false(is_dir($this->path('Repository/Framework/Roles')));
    }

    public function testRoleOrganizationClassificationContractIsPreserved(): void
    {
        $controller = $this->read('Repository/Framework/Users/Controllers/RolesController.php');
        $request = $this->read('Repository/Framework/Users/Requests/RolePayloadRequest.php');

        foreach (['hierarchy_scope_id', 'hierarchy_level_id', 'organization_unit_ids'] as $field) {
            Assert::contains($field, $controller);
            Assert::contains($field, $request);
        }
        Assert::contains('getRoleOrganizationUnitIds', $controller);
        Assert::contains('positiveIntList', $controller);
        Assert::contains('normalizeOrganizationUnitIds', $request);
    }

    /** @return list<string> */
    private function routeKeys(string $source): array
    {
        preg_match_all("/\\\$router->(get|post)\\('([^']+)'/", $source, $matches, \PREG_SET_ORDER);

        return array_map(
            static fn (array $match): string => strtoupper($match[1]) . ' ' . $match[2],
            $matches
        );
    }

    private function read(string $relative): string
    {
        $source = file_get_contents(
            dirname(__DIR__, 4) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relative)
        );
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$relative}.");
        }

        return $source;
    }

    private function path(string $relative): string
    {
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }
}
