<?php

declare(strict_types=1);

namespace CatalystAppTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class AppConsumersArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testAccountUsesTheCanonicalShellContract(): void
    {
        $viewModel = $this->read('Repository/App/Surface/Account/Support/AccountSurfaceViewModel.php');
        $styles = $this->read('public/assets/css/catalyst/account-shell.css');

        Assert::contains("'body_class' => 'catalyst-shell-body account-page-body'", $viewModel);
        Assert::contains("'shell_class' => 'wrapper'", $viewModel);
        Assert::contains("'topbar_class' => 'app-topbar'", $viewModel);
        Assert::contains("'sidebar_class' => 'sidenav-menu'", $viewModel);
        Assert::contains("'content_class' => 'content-page'", $viewModel);
        Assert::false(is_file($this->path('Repository/App/Surface/Account/Support/AccountShellViewModel.php')));
        Assert::false(str_contains($styles, 'account-shell-wrapper'));
        Assert::false(str_contains($styles, 'account-content-page'));
        Assert::false(str_contains($styles, 'account-sidenav'));
        Assert::false(str_contains($viewModel, 'navigation_model_data'));
        Assert::false(str_contains($viewModel, 'navGroups'));
        Assert::contains("'navigation_model' => 'application'", $viewModel);
    }

    public function testAppFunctionalSpecsLiveOnlyInTheAppSuite(): void
    {
        Assert::true(is_file($this->path('test/app/Playwright/specs/surface-account-layout.spec.cjs')));
        Assert::true(is_file($this->path('test/app/Playwright/specs/surface-public-layout.spec.cjs')));
        Assert::false(is_file($this->path('test/framework/Playwright/specs/surface-account-layout.spec.cjs')));
        Assert::false(is_file($this->path('test/framework/Playwright/specs/surface-public-layout.spec.cjs')));
    }

    private function read(string $path): string
    {
        $contents = file_get_contents($this->path($path));

        if (!is_string($contents)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $contents;
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
