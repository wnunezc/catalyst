<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class Roadmap4OwnershipContractTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testUsersAndAccountAreTheOnlyCanonicalOwners(): void
    {
        Assert::true(is_dir($this->path('Repository/Framework/Users')));
        Assert::true(is_dir($this->path('Repository/Framework/Account')));
        Assert::false(is_dir($this->path('Repository/Framework/Roles')));
        Assert::false(is_dir($this->path('Repository/App/Surface/Account')));

        Assert::same(31, count($this->routeKeys(
            $this->read('Repository/Framework/Users/routes.php')
        )));
        Assert::same(19, count($this->routeKeys(
            $this->read('Repository/Framework/Account/routes.php')
        )));
    }

    public function testInternalTransportsUseOnlyTheRuntimeFamily(): void
    {
        $routes = $this->allRouteKeys();
        $expected = [
            'GET /runtime/notifications',
            'GET /runtime/notifications/unread-count',
            'POST /runtime/notifications/read-all',
            'POST /runtime/notifications/{id}/read',
            'POST /runtime/presence/{resourceKey}/{recordId}/heartbeat',
            'GET /runtime/websocket/token',
            'POST /runtime/flash/dismiss',
        ];

        foreach ($expected as $routeKey) {
            Assert::true(isset($routes[$routeKey]), "Missing runtime route {$routeKey}.");
        }

        foreach (array_keys($routes) as $routeKey) {
            Assert::false(
                str_contains($routeKey, ' /api/notifications')
                    || str_contains($routeKey, ' /api/presence')
                    || str_contains($routeKey, ' /api/ws-token')
                    || str_contains($routeKey, ' /flash/dismiss'),
                "Legacy internal transport remains active: {$routeKey}."
            );
        }
    }

    public function testApplicationCompanionsAndIndexAliasesAreRemoved(): void
    {
        $routes = $this->allRouteKeys();

        foreach (array_keys($routes) as $routeKey) {
            Assert::false(
                str_contains($routeKey, ' /api/public/')
                    || str_ends_with($routeKey, ' /index')
                    || str_ends_with($routeKey, ' /index.php'),
                "Retired route remains active: {$routeKey}."
            );
        }
    }

    public function testUiShowcaseIsRemovedWhileUmlRemainsOwnedByDevTools(): void
    {
        $devToolsRoutes = array_fill_keys(
            $this->routeKeys($this->read('Repository/Framework/DevTools/routes.php')),
            true
        );

        Assert::false(isset($devToolsRoutes['GET /test-features/ui-showcase']));
        Assert::true(isset($devToolsRoutes['GET /uml']));
        Assert::false(is_file($this->path(
            'Repository/Framework/DevTools/Views/pages/ui-showcase.phtml'
        )));
    }

    public function testFrozenOwnersAndSurfacesKeepTheirRouteCounts(): void
    {
        Assert::same(29, $this->routeCount('Repository/Framework/Configuration/routes.php'));
        Assert::same(49, $this->routeCount('Repository/Framework/Workspaces/routes.php'));
        Assert::same(21, $this->routeCount('Repository/Framework/Operations/routes.php'));
        Assert::same(6, $this->routeCount('Repository/Framework/Api/routes.php'));
        Assert::same(40, $this->routeCount('Repository/Framework/DemoUi/routes.php'));

        $testFeatureRoutes = array_filter(
            $this->routeKeys($this->read('Repository/Framework/DevTools/routes.php')),
            static fn (string $routeKey): bool => str_contains($routeKey, ' /test-features')
        );
        Assert::same(42, count($testFeatureRoutes));
    }

    public function testFinalRouterInventoryContainsExactlyTwoHundredSixtyNineRoutes(): void
    {
        Assert::same(269, count($this->allRouteKeys()));
    }

    public function testCanonicalDocumentShellSidebarAndRuntimeRemainUnique(): void
    {
        Assert::true(is_file($this->path('boot-core/template/document.phtml')));
        Assert::true(is_file($this->path('boot-core/template/shell.phtml')));
        Assert::true(is_file($this->path('boot-core/template/_sidebar.phtml')));
        Assert::true(is_file($this->path('public/assets/js/catalyst/runtime/ui-runtime.js')));

        Assert::same(1, count($this->filesNamed('boot-core/template', 'document.phtml')));
        Assert::same(1, count($this->filesNamed('boot-core/template', 'shell.phtml')));
        Assert::same(1, count($this->filesNamed('boot-core/template', '_sidebar.phtml')));
        Assert::same(1, count($this->filesNamed('public/assets/js/catalyst', 'ui-runtime.js')));
    }

    /** @return array<string, true> */
    private function allRouteKeys(): array
    {
        $routes = [];
        $files = [$this->path('boot-core/routes/global-routes.php')];

        foreach (['Repository/Framework', 'Repository/App/Surface'] as $directory) {
            foreach ($this->phpFiles($directory) as $file) {
                if (basename($file) === 'routes.php') {
                    $files[] = $file;
                }
            }
        }

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            foreach ($this->routeKeys($this->readAbsolute($file)) as $routeKey) {
                Assert::false(isset($routes[$routeKey]), "Duplicate active route {$routeKey}.");
                $routes[$routeKey] = true;
            }
        }

        return $routes;
    }

    private function routeCount(string $relative): int
    {
        return count($this->routeKeys($this->read($relative)));
    }

    /** @return list<string> */
    private function routeKeys(string $source): array
    {
        preg_match_all(
            "/\\\$router->(get|post)\\('([^']+)'/",
            $source,
            $matches,
            \PREG_SET_ORDER
        );

        return array_map(
            static fn (array $match): string => strtoupper($match[1]) . ' ' . $match[2],
            $matches
        );
    }

    /** @return list<string> */
    private function phpFiles(string $directory): array
    {
        $path = $this->path($directory);
        if (!is_dir($path)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo
                && $file->isFile()
                && $file->getExtension() === 'php'
            ) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /** @return list<string> */
    private function filesNamed(string $directory, string $name): array
    {
        $path = $this->path($directory);
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getFilename() === $name) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function path(string $relative): string
    {
        return $this->root . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    private function read(string $relative): string
    {
        return $this->readAbsolute($this->path($relative));
    }

    private function readAbsolute(string $path): string
    {
        $source = file_get_contents($path);
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
