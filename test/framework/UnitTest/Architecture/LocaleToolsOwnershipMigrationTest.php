<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class LocaleToolsOwnershipMigrationTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testLocaleToolsHasFourCanonicalWorkspacesRoutes(): void
    {
        $routes = $this->read('Repository/Framework/Workspaces/routes.php');

        Assert::same(4, $this->routeCount($routes, '/workspaces/locale-tools'));
        Assert::contains('Catalyst\\Repository\\Workspaces\\Localization\\Controllers\\LocalizationController', $routes);
        Assert::contains('WorkspacesAccessContract::LOCALIZATION', $routes);
        Assert::false(str_contains($routes, "get('/workspaces/locale-tools/settings'"));
    }

    public function testLocaleToolsUsesValidatedRequestsAndSafeCatalogWrites(): void
    {
        foreach ([
            'LocaleCreateRequest.php',
            'LocaleSyncRequest.php',
            'LocalizationSettingsRequest.php',
        ] as $requestFile) {
            Assert::contains(
                'extends FormRequest',
                $this->read('Repository/Framework/Workspaces/Localization/Requests/' . $requestFile)
            );
        }

        $controller = $this->read(
            'Repository/Framework/Workspaces/Localization/Controllers/LocalizationController.php'
        );
        $manager = $this->read('app/Framework/Localization/LocalizationManager.php');

        Assert::false(str_contains($controller, "'admin'"));
        Assert::false(str_contains($controller, 'operations.localization'));
        Assert::contains('AtomicLocaleCatalogWriter', $manager);
        Assert::contains('assertMutableLocale', $manager);
        Assert::contains('RecursiveDirectoryIterator', $manager);
    }

    private function routeCount(string $source, string $prefix): int
    {
        preg_match_all(
            "/\\\$router->(?:get|post)\\('" . preg_quote($prefix, '/') . "(?:'|\\/)/",
            $source,
            $matches
        );

        return count($matches[0]);
    }

    private function read(string $relativePath): string
    {
        $source = @file_get_contents($this->root . '/' . $relativePath);

        return is_string($source) ? $source : '';
    }
}
