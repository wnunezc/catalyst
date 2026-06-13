<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class DocumentTemplatesOwnershipMigrationTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testDocumentTemplatesMovesAllHtmlAndPublicApiRoutesToWorkspaces(): void
    {
        Assert::false(is_dir($this->root . '/Repository/Framework/Documents'));
        Assert::true(is_dir($this->root . '/Repository/Framework/Workspaces/Documents'));

        $routes = $this->read('Repository/Framework/Workspaces/routes.php');
        Assert::same(11, $this->routeCount($routes, '/workspaces/document-templates'));
        Assert::same(4, $this->routeCount($routes, '/api/v1/document-templates'));
        Assert::contains(
            'Catalyst\\Repository\\Workspaces\\Documents\\Controllers\\DocumentTemplateController',
            $routes
        );
        Assert::contains(
            'Catalyst\\Repository\\Workspaces\\Documents\\Controllers\\DocumentTemplateApiController',
            $routes
        );
        Assert::contains('WorkspacesAccessContract::DOCUMENT_TEMPLATES', $routes);
    }

    public function testPublicApiTransportContractAndAbilitiesRemainStable(): void
    {
        $routes = $this->read('Repository/Framework/Workspaces/routes.php');
        $catalog = $this->read('app/Framework/Api/ApiCatalog.php');
        $plugin = $this->read('boot-core/plugins/framework.business.php');

        Assert::contains('ApiTokenMiddleware::class', $routes);
        Assert::same(2, substr_count($routes, "->throttle('api_mutation')"));
        Assert::contains(
            "'permission' => 'manage-workspaces-document-templates'",
            $catalog
        );
        Assert::contains(
            "'permission' => 'manage-workspaces-document-templates|manage-operations-automation-rules'",
            $catalog
        );
        Assert::false(str_contains($plugin, "'framework.documents'"));
        Assert::contains("'framework.workspaces'", $plugin);
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
