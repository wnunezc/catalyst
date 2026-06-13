<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class Roadmap4PrivilegedRecoveryOwnershipTest extends TestCase
{
    public function testPrivilegedRecoveryBelongsOnlyToUsers(): void
    {
        $users = $this->read('Repository/Framework/Users/routes.php');
        $account = $this->read('Repository/Framework/Account/routes.php');

        Assert::same(4, substr_count($users, "'/users/account-recovery"));
        Assert::contains("new RoleMiddleware(permissions: 'manage-account-recovery')", $users);
        Assert::contains('AccountRecoveryReviewController', $users);
        $retiredSegment = '/' . 'ad' . 'min/';
        Assert::false(str_contains($account, $retiredSegment . 'account-recovery'));
        Assert::false(str_contains($users, "'{$retiredSegment}"));
        Assert::false(is_file($this->path('Repository/Framework/Users/Views/pages/' . 'ad' . 'min-show.phtml')));
        Assert::true(is_file($this->path('Repository/Framework/Users/Views/pages/recovery-review.phtml')));
        Assert::true(is_file($this->path('Repository/Framework/Account/Repositories/AccountRecoveryRepository.php')));
        Assert::false(is_dir($this->path('Repository/App/Surface/Account')));
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
