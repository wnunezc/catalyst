<?php

declare(strict_types=1);

namespace CatalystTest\Account;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ProfileAvatarContractTest extends TestCase
{
    public function testAccountProfileExposesAvatarUploadAndPersistenceContract(): void
    {
        $view = $this->source('Repository/Framework/Account/Views/pages/profile.phtml');
        $routes = $this->source('Repository/Framework/Account/routes.php');
        $controller = $this->source('Repository/Framework/Account/Controllers/AccountCenterController.php');
        $profile = $this->source('app/Entities/UserProfile.php');
        $migration = $this->source('boot-core/database/migrations/20260619090000_add_avatar_path_to_user_profiles.php');

        Assert::contains('name="avatar"', $view);
        Assert::contains('enctype="multipart/form-data"', $view);
        Assert::contains('/account/profile/avatar', $routes);
        Assert::contains('updateAvatar', $controller);
        Assert::contains('avatar_path', $profile);
        Assert::contains('ADD COLUMN `avatar_path`', $migration);
    }

    private function source(string $path): string
    {
        $root = defined('PD') ? \PD : getcwd();
        $contents = file_get_contents($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));

        return is_string($contents) ? $contents : '';
    }
}
