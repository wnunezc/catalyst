<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class MediaFieldsOwnershipMigrationTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testMediaFieldsAndLibraryHaveSingleWorkspacesOwner(): void
    {
        $workspacesRoutes = $this->read('Repository/Framework/Workspaces/routes.php');

        Assert::same(6, $this->routeCount($workspacesRoutes, '/workspaces/media-fields'));
        Assert::same(7, $this->routeCount($workspacesRoutes, '/workspaces/media-library'));
        Assert::contains(
            'Catalyst\\Repository\\Workspaces\\Media\\Controllers\\MetadataFieldController',
            $workspacesRoutes
        );
        Assert::contains('WorkspacesAccessContract::MEDIA_FIELDS', $workspacesRoutes);
    }

    public function testMediaFieldsUsesCanonicalNamespacePermissionAndGlobalFormBuilder(): void
    {
        $controller = $this->read(
            'Repository/Framework/Workspaces/Media/Controllers/MetadataFieldController.php'
        );
        $factory = $this->read(
            'Repository/Framework/Workspaces/Media/Support/MetadataFieldFormFactory.php'
        );
        $workspacesManifest = $this->read('Repository/Framework/Workspaces/module.php');

        Assert::contains(
            'namespace Catalyst\\Repository\\Workspaces\\Media\\Controllers;',
            $controller
        );
        Assert::contains('use Catalyst\\Framework\\Form\\FormBuilder;', $factory);
        Assert::contains("'href' => '/workspaces/media-fields'", $workspacesManifest);
        Assert::contains("'manage-workspaces-media-fields'", $workspacesManifest);
        Assert::false(is_dir($this->root . '/Repository/Framework/Media'));
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
        $path = $this->root . '/' . $relativePath;
        $source = is_file($path) ? file_get_contents($path) : false;

        return is_string($source) ? $source : '';
    }
}
