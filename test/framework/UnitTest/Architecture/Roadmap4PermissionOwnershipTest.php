<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class Roadmap4PermissionOwnershipTest extends TestCase
{
    public function testUsersOwnsTheThreeCanonicalPermissionDeclarations(): void
    {
        $users = require dirname(__DIR__, 4) . '/Repository/Framework/Users/module.php';
        $account = require dirname(__DIR__, 4) . '/Repository/Framework/Account/module.php';

        Assert::same(
            ['manage-account-recovery', 'manage-roles', 'manage-users'],
            $this->permissionSlugs($users)
        );
        Assert::same([], $this->permissionSlugs($account));
        Assert::true(!in_array('manage-account', $this->permissionSlugs($users), true));
        Assert::false(is_dir(dirname(__DIR__, 4) . '/Repository/Framework/Roles'));
    }

    /**
     * @param array<string, mixed> $module
     * @return list<string>
     */
    private function permissionSlugs(array $module): array
    {
        $slugs = array_values(array_filter(array_map(
            static fn (array $permission): string => (string) ($permission['slug'] ?? ''),
            (array) ($module['permissions'] ?? [])
        )));
        sort($slugs);

        return $slugs;
    }
}
