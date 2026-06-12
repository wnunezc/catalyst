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

    public function testAuthenticatedSuitesUseOneWorker(): void
    {
        foreach ([
            'test/framework/Playwright/playwright.config.cjs',
            'test/app/Playwright/playwright.config.cjs',
        ] as $path) {
            $config = $this->read($path);
            Assert::contains('fullyParallel: false', $config, "{$path} must not run authenticated specs in parallel.");
            Assert::contains('workers: 1', $config, "{$path} must use the shared authenticated account serially.");
        }
    }

    public function testPreparedSpecsUseCurrentSurfaceContracts(): void
    {
        $devTools = $this->read('test/framework/Playwright/specs/devtools-modals.spec.cjs');
        $flash = $this->read('test/framework/Playwright/specs/flash-runtime.spec.cjs');
        $presence = $this->read('test/framework/Playwright/specs/record-presence-runtime.spec.cjs');
        $dataGrid = $this->read('test/framework/Playwright/specs/datagrid-runtime.spec.cjs');

        Assert::contains('data-devtools-action', $devTools);
        Assert::false(str_contains($devTools, '[data-action='));
        Assert::contains("openSurface(page, expect, '/test-features'", $flash);
        Assert::false(str_contains($flash, "openSurface(page, expect, '/test-features/flash/clear'"));
        Assert::contains("href || ''", $presence);
        Assert::contains('automation-rules', $presence);
        Assert::contains('\\d+', $presence);
        Assert::contains("locator('[data-bs-toggle=\"dropdown\"]')", $dataGrid);
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
