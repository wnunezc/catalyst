<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class WorkspacesOperationsOwnershipContractTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testCanonicalOwnersReplaceLegacyRepositoryModules(): void
    {
        Assert::true(is_dir($this->path('Repository/Framework/Workspaces')));
        Assert::true(is_dir($this->path('Repository/Framework/Operations')));

        foreach (['Catalogs', 'Media', 'Documents', 'Audit', 'ApiPlatform', 'Automation'] as $legacyModule) {
            Assert::false(
                is_dir($this->path('Repository/Framework/' . $legacyModule)),
                "Legacy Repository module remains active: {$legacyModule}."
            );
        }
    }

    public function testExactlySeventySixRoutesBelongToTheCanonicalOwners(): void
    {
        $actual = [];

        foreach (['Workspaces', 'Operations', 'Api'] as $owner) {
            $routesFile = $this->path("Repository/Framework/{$owner}/routes.php");
            Assert::true(is_file($routesFile), "Missing canonical routes file for {$owner}.");

            foreach ($this->routeKeys($this->read($routesFile)) as $routeKey) {
                Assert::false(isset($actual[$routeKey]), "Duplicate canonical route {$routeKey}.");
                $actual[$routeKey] = $owner;
            }
        }

        $expected = array_fill_keys($this->expectedRouteKeys(), true);
        ksort($actual);
        ksort($expected);

        Assert::same(76, count($actual));
        Assert::same(array_keys($expected), array_keys($actual));
    }

    public function testOperationsHtmlRoutesUseOnlyTheCanonicalFamily(): void
    {
        $routesFile = $this->path('Repository/Framework/Operations/routes.php');
        Assert::true(is_file($routesFile), 'Missing canonical Operations routes file.');
        $operationsRoutes = $this->read($routesFile);

        foreach ([
            '/audit-log',
            '/api-platform',
            '/automation-rules',
        ] as $legacyPrefix) {
            Assert::false(
                preg_match(
                    "/\\\$router->(?:get|post)\\('" . preg_quote($legacyPrefix, '/') . "(?:'|\\/)/",
                    $operationsRoutes
                ) === 1,
                "Legacy Operations URL remains active: {$legacyPrefix}."
            );
        }

        Assert::false(
            preg_match("/\\\$router->(?:get|post)\\('\\/operations'/", $operationsRoutes) === 1,
            'The Operations group must not become an overview route.'
        );
    }

    public function testCanonicalPermissionsReplaceLegacyPermissions(): void
    {
        $workspacesManifest = $this->path('Repository/Framework/Workspaces/module.php');
        $operationsManifest = $this->path('Repository/Framework/Operations/module.php');
        Assert::true(is_file($workspacesManifest), 'Missing canonical Workspaces manifest.');
        Assert::true(is_file($operationsManifest), 'Missing canonical Operations manifest.');
        $manifests = $this->read($workspacesManifest) . $this->read($operationsManifest);

        foreach ($this->canonicalPermissions() as $permission) {
            Assert::contains("'slug' => '{$permission}'", $manifests);
        }

        $activeSource = $this->activeSource();
        foreach ($this->legacyPermissions() as $permission) {
            Assert::false(
                str_contains($activeSource, $permission),
                "Legacy permission remains active: {$permission}."
            );
        }
    }

    public function testPublicVersionedApiRoutesRemainStable(): void
    {
        $expected = array_fill_keys($this->publicApiRouteKeys(), true);
        $actual = [];

        foreach ($this->phpFiles('Repository/Framework') as $file) {
            if (basename($file) !== 'routes.php') {
                continue;
            }

            foreach ($this->routeKeys($this->read($file)) as $routeKey) {
                if (str_contains($routeKey, ' /api/v1/')) {
                    $actual[$routeKey] = true;
                }
            }
        }

        ksort($actual);
        ksort($expected);

        Assert::same(13, count($actual));
        Assert::same(array_keys($expected), array_keys($actual));
    }

    /** @return list<string> */
    private function expectedRouteKeys(): array
    {
        return [
            'GET /workspaces/catalogs',
            'POST /workspaces/catalogs',
            'GET /workspaces/catalogs/create',
            'GET /workspaces/catalogs/{id}',
            'POST /workspaces/catalogs/{id}',
            'POST /workspaces/catalogs/{id}/delete',
            'GET /workspaces/catalogs/{id}/edit',
            'GET /workspaces/catalogs/{id}/items/create',
            'POST /workspaces/catalogs/{id}/items',
            'GET /workspaces/catalogs/{id}/items/{itemId}/edit',
            'POST /workspaces/catalogs/{id}/items/{itemId}',
            'POST /workspaces/catalogs/{id}/items/{itemId}/delete',
            'POST /workspaces/catalogs/{id}/transition',
            'POST /workspaces/catalogs/{id}/versions/{versionId}/restore',
            'GET /workspaces/media-fields',
            'POST /workspaces/media-fields',
            'GET /workspaces/media-fields/create',
            'GET /workspaces/media-fields/{id}/edit',
            'POST /workspaces/media-fields/{id}',
            'POST /workspaces/media-fields/{id}/delete',
            'GET /workspaces/media-library',
            'POST /workspaces/media-library',
            'GET /workspaces/media-library/upload',
            'POST /workspaces/media-library/bulk-delete',
            'GET /workspaces/media-library/{id}/edit',
            'POST /workspaces/media-library/{id}',
            'POST /workspaces/media-library/{id}/delete',
            'GET /workspaces/document-templates',
            'POST /workspaces/document-templates',
            'GET /workspaces/document-templates/create',
            'GET /workspaces/document-templates/{id}',
            'POST /workspaces/document-templates/{id}',
            'POST /workspaces/document-templates/{id}/delete',
            'GET /workspaces/document-templates/{id}/edit',
            'POST /workspaces/document-templates/{id}/preview',
            'POST /workspaces/document-templates/{id}/export',
            'POST /workspaces/document-templates/{id}/transition',
            'POST /workspaces/document-templates/{id}/versions/{versionId}/restore',
            'GET /api/v1/document-templates',
            'GET /api/v1/document-templates/{id}',
            'POST /api/v1/document-templates/{id}/preview',
            'POST /api/v1/document-templates/{id}/export',
            'GET /workspaces/module-designer',
            'POST /workspaces/module-designer/preview',
            'POST /workspaces/module-designer/generate',
            'GET /workspaces/locale-tools',
            'POST /workspaces/locale-tools/settings',
            'POST /workspaces/locale-tools/create-locale',
            'POST /workspaces/locale-tools/sync-locale',
            'GET /operations/audit-log',
            'GET /operations/audit-log/{id}',
            'GET /operations/api-management',
            'POST /operations/api-management/tokens',
            'POST /operations/api-management/tokens/{id}/revoke',
            'GET /api/v1/catalog',
            'GET /api/v1/calendar/events',
            'GET /api/v1/workflows',
            'POST /api/v1/workflows/{id}/transition',
            'GET /api/v1/versions/{resourceKey}/{recordId}',
            'POST /api/v1/versions/{id}/restore',
            'GET /operations/automation-rules',
            'POST /operations/automation-rules',
            'GET /operations/automation-rules/create',
            'GET /operations/automation-rules/{id}',
            'POST /operations/automation-rules/{id}',
            'POST /operations/automation-rules/{id}/delete',
            'GET /operations/automation-rules/{id}/edit',
            'POST /operations/automation-rules/{id}/run',
            'POST /operations/automation-rules/{id}/transition',
            'POST /operations/automation-rules/{id}/versions/{versionId}/restore',
            'GET /api/v1/automation-rules',
            'GET /api/v1/automation-rules/{id}',
            'POST /api/v1/automation-rules/{id}/run',
            'GET /operations/deployments',
            'POST /operations/deployments/runs',
            'GET /operations/tenancy',
        ];
    }

    /** @return list<string> */
    private function publicApiRouteKeys(): array
    {
        return array_values(array_filter(
            $this->expectedRouteKeys(),
            static fn (string $routeKey): bool => str_contains($routeKey, ' /api/v1/')
        ));
    }

    /** @return list<string> */
    private function canonicalPermissions(): array
    {
        return [
            'manage-workspaces-catalogs',
            'manage-workspaces-module-designer',
            'manage-workspaces-media-fields',
            'manage-workspaces-media-library',
            'manage-workspaces-document-templates',
            'manage-workspaces-localization',
            'manage-operations-deployments',
            'manage-operations-tenancy',
            'manage-operations-audit-log',
            'manage-operations-api-management',
            'manage-operations-automation-rules',
        ];
    }

    /** @return list<string> */
    private function legacyPermissions(): array
    {
        return [
            'manage-catalogs',
            'manage-media-metadata',
            'manage-media-library',
            'manage-document-templates',
            'manage-audit-log',
            'manage-api-platform',
            'manage-automation-rules',
            'manage-platform-operations',
        ];
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

    private function activeSource(): string
    {
        $source = '';

        foreach (['app', 'Repository', 'boot-core'] as $directory) {
            foreach ($this->phpFiles($directory) as $file) {
                if (str_contains($file, DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
                    || str_contains($file, 'CanonicalPermissionGrantMigrator.php')
                    || str_contains($file, 'LegacyWorkspacePermissionRetirer.php')
                    || str_contains($file, 'LegacyOperationsPermissionRetirer.php')
                ) {
                    continue;
                }

                $source .= $this->read($file);
            }
        }

        return $source;
    }

    /** @return list<string> */
    private function phpFiles(string $directory): array
    {
        $files = [];
        $path = $this->path($directory);
        if (!is_dir($path)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
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

    private function read(string $path): string
    {
        $source = file_get_contents($path);
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
