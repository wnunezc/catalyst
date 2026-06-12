const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@datagrid Neutral DataGrid runtime', () => {
    test('real consumer uses the neutral template, assets and central interactions', async ({ page }) => {
        try {
            await openSurface(page, expect, '/users', { requireTriggers: false });

            const grid = page.locator('[data-datagrid]').first();
            await expect(grid).toBeVisible();
            await expect(grid).toHaveClass(/datagrid-card/);
            await expect(page.locator('[data-admin-grid], .admin-grid-table, .admin-datagrid-card')).toHaveCount(0);
            await expect(page.locator('link[href*="/assets/css/catalyst/datagrid.css"]')).toHaveCount(1);
            await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);

            const perPage = grid.locator('[data-grid-per-page]');
            if (await perPage.count()) {
                await expect(perPage).toBeEnabled();
            }

            const print = grid.locator('[data-grid-print]');
            if (await print.count()) {
                const tools = print.locator('xpath=ancestor::*[contains(concat(" ", normalize-space(@class), " "), " dropdown ")][1]');
                await tools.locator('[data-bs-toggle="dropdown"]').click();
                await expect(print).toBeVisible();
                await page.evaluate(() => {
                    window.__catalystPrintCalls = 0;
                    window.print = () => {
                        window.__catalystPrintCalls += 1;
                    };
                });
                await print.click();
                await expect.poll(() => page.evaluate(() => window.__catalystPrintCalls)).toBe(1);
            }

            const rows = grid.locator('[data-grid-row-checkbox]');
            if (await rows.count()) {
                const first = rows.first();
                await first.check();
                await expect(grid.locator('[data-grid-bulk-action]').first()).toBeEnabled();
                await expect(grid.locator('[data-grid-selection-summary]')).not.toHaveText(/No rows selected|No hay filas seleccionadas/);
                await first.uncheck();
                await expect(grid.locator('[data-grid-bulk-action]').first()).toBeDisabled();
            }
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }
    });
});
