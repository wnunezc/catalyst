<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class PlaywrightPreparationArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testPlaywrightSuitesSeparateParallelReadOnlyCoverageFromSerialStatefulFlows(): void
    {
        foreach ([
            'test/framework/Playwright/playwright.config.cjs',
            'test/app/Playwright/playwright.config.cjs',
        ] as $path) {
            $config = $this->read($path);
            Assert::contains("name: 'surface-parallel'", $config, "{$path} must expose parallel read-only coverage.");
            Assert::contains("name: 'stateful-serial'", $config, "{$path} must isolate stateful flows.");
            Assert::contains('workers: parallelWorkers', $config, "{$path} must use the configured read-only worker pool.");
            Assert::contains('workers: 1', $config, "{$path} must keep stateful flows serial.");
        }

        Assert::contains(
            'dependencies:',
            $this->read('test/framework/Playwright/playwright.config.cjs'),
            'Framework parallel coverage must prepare isolated authentication.'
        );
    }

    public function testRoadmap7InventoryClassifiesEveryIncludedRouteExactlyOnce(): void
    {
        $inventory = $this->read('test/framework/Playwright/fixtures/roadmap7-surface-inventory.cjs');

        Assert::contains('const expectedIncludedRouteCount = 117', $inventory);
        Assert::contains("'parallel-readonly'", $inventory);
        Assert::contains("'serial-stateful'", $inventory);
        Assert::contains("'html'", $inventory);
        Assert::contains("'transport'", $inventory);
        Assert::contains("'flow'", $inventory);
        Assert::contains("'app'", $inventory);
        Assert::contains("'framework'", $inventory);
        Assert::contains('assertCompleteInventory', $inventory);
    }

    public function testParallelCoverageUsesAnIsolatedAuthenticatedSessionPerWorker(): void
    {
        $authPool = $this->read('test/framework/Playwright/helpers/auth-pool.cjs');
        $parallel = $this->read('test/framework/Playwright/helpers/parallel-playwright.cjs');

        Assert::contains('createAuthPool', $authPool);
        Assert::contains('storageState', $authPool);
        Assert::contains('parallelIndex', $parallel);
        Assert::contains('authStatePath', $parallel);
        Assert::contains('browser.newContext', $parallel);
    }

    public function testPreparedSpecsUseCurrentSurfaceContracts(): void
    {
        $devTools = $this->read('test/framework/Playwright/specs/devtools-modals.spec.cjs');
        $flash = $this->read('test/framework/Playwright/specs/flash-runtime.spec.cjs');
        $presence = $this->read('test/framework/Playwright/specs/record-presence-runtime.spec.cjs');
        $dataGrid = $this->read('test/framework/Playwright/specs/datagrid-runtime.spec.cjs');
        $composition = $this->read('test/framework/Playwright/specs/roadmap7-surface-composition.spec.cjs');
        $remainingTables = $this->read('test/framework/Playwright/specs/roadmap7-remaining-tables-audit.spec.cjs');

        Assert::contains('data-devtools-action', $devTools);
        Assert::false(str_contains($devTools, '[data-action='));
        Assert::contains("openSurface(page, expect, '/test-features'", $flash);
        Assert::false(str_contains($flash, "openSurface(page, expect, '/test-features/flash/clear'"));
        Assert::contains("href || ''", $presence);
        Assert::contains('automation-rules', $presence);
        Assert::contains('\\d+', $presence);
        Assert::contains("locator('[data-bs-toggle=\"dropdown\"]')", $dataGrid);
        Assert::contains('not.toHaveClass(/datagrid-card/)', $dataGrid);
        Assert::contains('@roadmap7-surface-composition', $composition);
        Assert::contains('.card-header, .card-body, .card-footer', $composition);
        Assert::contains('[data-datagrid] table.table-nowrap', $composition);
        Assert::contains('@roadmap7-remaining-tables', $remainingTables);
        Assert::contains("'/test-features'", $remainingTables);
        Assert::contains("'/operations/automation-rules/{id}'", $remainingTables);
        Assert::contains("'/workspaces/catalogs/{id}'", $remainingTables);
        Assert::contains("'/workspaces/document-templates/{id}'", $remainingTables);
        Assert::contains('hasResponsiveWrapper', $remainingTables);
        Assert::contains('bodyFlush', $remainingTables);
    }

    private function read(string $path): string
    {
        $source = file_get_contents(
            $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
        );

        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
