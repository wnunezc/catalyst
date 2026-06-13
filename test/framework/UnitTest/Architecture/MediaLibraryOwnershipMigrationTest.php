<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use Catalyst\Repository\Workspaces\Media\Controllers\MediaLibraryController;
use Catalyst\Repository\Workspaces\Media\Requests\MediaBulkSelectionRequest;
use Catalyst\Repository\Workspaces\Media\Requests\MediaItemRequest;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class MediaLibraryOwnershipMigrationTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testMediaLibraryClosesTheLegacyMediaModule(): void
    {
        Assert::false(is_dir($this->root . '/Repository/Framework/Media'));
        Assert::true(is_dir($this->root . '/Repository/Framework/Workspaces/Media'));

        $routes = $this->read('Repository/Framework/Workspaces/routes.php');
        Assert::same(7, $this->routeCount($routes, '/workspaces/media-library'));
        Assert::contains(
            'Catalyst\\Repository\\Workspaces\\Media\\Controllers\\MediaLibraryController',
            $routes
        );
        Assert::contains('WorkspacesAccessContract::MEDIA_LIBRARY', $routes);
        Assert::contains("'/workspaces/media-library/bulk-delete'", $routes);
        Assert::contains("'/workspaces/media-library/upload'", $routes);
    }

    public function testMediaLibraryNamespacePluginAndAssetsUseWorkspacesOwner(): void
    {
        $controller = $this->read(
            'Repository/Framework/Workspaces/Media/Controllers/MediaLibraryController.php'
        );
        $plugin = $this->read('boot-core/plugins/framework.business.php');
        $manifest = $this->read('Repository/Framework/Workspaces/module.php');

        Assert::contains(
            'namespace Catalyst\\Repository\\Workspaces\\Media\\Controllers;',
            $controller
        );
        Assert::contains('MediaBulkSelectionRequest', $controller);
        Assert::true(class_exists(MediaItemRequest::class));
        Assert::true(class_exists(MediaBulkSelectionRequest::class));
        Assert::true(method_exists(MediaLibraryController::class, 'store'));
        Assert::true(method_exists(MediaLibraryController::class, 'bulkDestroy'));
        Assert::contains("'framework.workspaces'", $plugin);
        Assert::false(str_contains($plugin, "'framework.media'"));
        Assert::contains("'manage-workspaces-media-library'", $manifest);
        Assert::false(is_dir($this->root . '/public/assets/css/work/media'));
        Assert::false(is_dir($this->root . '/public/assets/js/work/media'));
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
