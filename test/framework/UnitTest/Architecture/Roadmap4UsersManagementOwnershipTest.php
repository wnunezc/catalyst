<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class Roadmap4UsersManagementOwnershipTest extends TestCase
{
    public function testUsersOwnsTheThreeManagementAndEnrollmentRoutes(): void
    {
        $users = $this->read('/Repository/Framework/Users/routes.php');

        Assert::contains(
            "use Catalyst\\Repository\\Users\\Controllers\\UserManagementController;",
            $users
        );
        Assert::contains("\$router->get('/users',", $users);
        Assert::contains("\$router->get('/users/enroll',", $users);
        Assert::contains("\$router->post('/users/enroll',", $users);
        Assert::contains("new RoleMiddleware(permissions: 'manage-users')", $users);
        Assert::contains("->throttle('privileged_mutation')", $users);

        Assert::false(is_dir(dirname(__DIR__, 4) . '/Repository/Framework/Roles'));
    }

    public function testManagementClassesAndViewsExistOnlyUnderUsers(): void
    {
        $root = dirname(__DIR__, 4);
        $usersPaths = [
            '/Repository/Framework/Users/Controllers/UserManagementController.php',
            '/Repository/Framework/Users/Requests/UserEnrollmentRequest.php',
            '/Repository/Framework/Users/Support/UserEnrollmentFormFactory.php',
            '/Repository/Framework/Users/Views/pages/users-index.phtml',
            '/Repository/Framework/Users/Views/pages/user-register.phtml',
        ];
        $legacyPaths = [
            '/Repository/Framework/Roles/Controllers/UserManagementController.php',
            '/Repository/Framework/Roles/Requests/UserEnrollmentRequest.php',
            '/Repository/Framework/Roles/Support/UserEnrollmentFormFactory.php',
            '/Repository/Framework/Roles/Views/pages/users-index.phtml',
            '/Repository/Framework/Roles/Views/pages/user-register.phtml',
        ];

        foreach ($usersPaths as $path) {
            Assert::true(is_file($root . $path), "Missing Users artifact {$path}.");
        }
        foreach ($legacyPaths as $path) {
            Assert::false(is_file($root . $path), "Legacy Roles artifact remains {$path}.");
        }
    }

    private function read(string $relative): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . $relative);
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$relative}.");
        }

        return $source;
    }
}
