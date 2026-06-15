const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@datagrid Neutral DataGrid runtime', () => {
    test('real consumer uses the neutral template, assets and central interactions', async ({ page }) => {
        try {
            await openSurface(page, expect, '/users', { requireTriggers: false });

            const grid = page.locator('[data-datagrid]').first();
            await expect(grid).toBeVisible();
            await expect(grid).toHaveClass(/card/);
            await expect(grid).not.toHaveClass(/datagrid-card/);
            await expect(page.locator('[data-admin-grid], .admin-grid-table, .admin-datagrid-card')).toHaveCount(0);
            await expect(page.locator('link[href*="/assets/css/catalyst/datagrid.css"]')).toHaveCount(1);
            await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);

            const runtimeSrc = await page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]').getAttribute('src');
            const runtimeVersion = new URL(runtimeSrc, page.url()).search;
            const servedInteractions = await page.evaluate(async (version) => {
                const response = await fetch(`/assets/js/catalyst/datagrid/interactions.js${version}`, { cache: 'no-store' });
                return response.text();
            }, runtimeVersion);
            expect(servedInteractions).toContain('copyWithFallback');

            const perPage = grid.locator('[data-grid-per-page]');
            const perPageCount = await perPage.count();
            for (let index = 0; index < perPageCount; index++) {
                await expect(perPage.nth(index)).toBeEnabled();
            }

            const toolbars = grid.locator('.datagrid-toolbar');
            await expect(toolbars).toHaveCount(2);
            await expect(grid.locator('.datagrid-toolbar [data-grid-per-page]')).toHaveCount(2);
            await expect(grid.locator('.datagrid-toolbar nav[aria-label]')).toHaveCount(2);

            const toolbarGeometry = await grid.locator('.datagrid-toolbar').evaluateAll((elements) => elements.map((toolbar) => {
                const tableWrapper = toolbar.closest('[data-datagrid]').querySelector('.datagrid-table').closest('.table-responsive');
                const toolbarRect = toolbar.getBoundingClientRect();
                const tableRect = tableWrapper.getBoundingClientRect();

                return {
                    leftDelta: Math.abs(toolbarRect.left - tableRect.left),
                    rightDelta: Math.abs(toolbarRect.right - tableRect.right),
                };
            }));

            for (const toolbar of toolbarGeometry) {
                expect(toolbar.leftDelta).toBeLessThanOrEqual(1);
                expect(toolbar.rightDelta).toBeLessThanOrEqual(1);
            }

            const print = grid.locator('[data-grid-print]');
            const printCount = await print.count();
            if (printCount) {
                await page.evaluate(() => {
                    window.__catalystPrintCalls = 0;
                    window.print = () => {
                        window.__catalystPrintCalls += 1;
                    };
                });

                for (let index = 0; index < printCount; index++) {
                    const trigger = print.nth(index);
                    const tools = trigger.locator('xpath=ancestor::*[contains(concat(" ", normalize-space(@class), " "), " dropdown ")][1]');
                    await tools.locator('[data-bs-toggle="dropdown"]').click();
                    await expect(trigger).toBeVisible();
                    await trigger.click();
                    await expect.poll(() => page.evaluate(() => window.__catalystPrintCalls)).toBe(index + 1);
                }
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

            await openSurface(page, expect, '/users/permissions', { requireTriggers: false });
            const automaticGrid = page.locator('[data-datagrid]').first();
            const copy = automaticGrid.locator('[data-grid-copy]').first();
            await expect(copy).toBeVisible();

            const fullValue = await copy.getAttribute('data-grid-copy-value');
            const compactText = copy.locator('xpath=preceding-sibling::*[contains(concat(" ", normalize-space(@class), " "), " datagrid-cell-text ")][1]');

            expect(fullValue.length).toBeGreaterThan(35);
            await expect(compactText).toHaveAttribute('data-bs-toggle', 'tooltip');
            await expect(compactText).toHaveAttribute('data-bs-original-title', fullValue);
            await page.context().grantPermissions(['clipboard-read', 'clipboard-write'], {
                origin: new URL(page.url()).origin,
            });
            await copy.click();
            await expect.poll(() => page.evaluate(() => navigator.clipboard.readText())).toBe(fullValue);
            await expect(copy).toHaveAttribute('aria-label', /Copied|Copiado/);
            await page.evaluate(() => {
                window.__catalystCopiedValue = null;
                Object.defineProperty(navigator, 'clipboard', {
                    configurable: true,
                    value: {
                        writeText(value) {
                            window.__catalystCopiedValue = value;
                            return Promise.resolve();
                        },
                    },
                });
            });
            await copy.click();
            await expect.poll(() => page.evaluate(() => window.__catalystCopiedValue)).toBe(fullValue);

            await page.evaluate(() => {
                window.__catalystFallbackCopiedValue = null;
                navigator.clipboard.writeText = () => Promise.reject(new DOMException('Clipboard permission denied', 'NotAllowedError'));
                document.execCommand = (command) => {
                    if (command !== 'copy') {
                        return false;
                    }

                    window.__catalystFallbackCopiedValue = document.querySelector('textarea.visually-hidden')?.value ?? null;
                    return true;
                };
            });
            await copy.click();
            await expect.poll(() => page.evaluate(() => window.__catalystFallbackCopiedValue)).toBe(fullValue);

            await openSurface(page, expect, '/operations/audit-log', { requireTriggers: false });
            const auditGrid = page.locator('[data-datagrid]').first();
            const requestCopy = auditGrid.locator('[data-grid-stack-line="secondary"] [data-grid-copy][data-grid-copy-value^="/"]').first();
            await expect(requestCopy).toBeVisible();

            const requestValue = await requestCopy.getAttribute('data-grid-copy-value');
            const requestText = requestCopy.locator('xpath=preceding-sibling::*[contains(concat(" ", normalize-space(@class), " "), " datagrid-cell-text ")][1]');
            const auditTableScroll = auditGrid.locator('.datagrid-table-scroll');
            const visibleColumns = Number(await auditTableScroll.getAttribute('data-grid-visible-columns'));
            const visibleCharacterLimit = Number(await auditTableScroll.getAttribute('data-grid-visible-character-limit'));
            const expectedCharacterLimit = Math.max(15, 35 - (Math.max(0, visibleColumns - 6) * 5));

            expect(visibleCharacterLimit).toBe(expectedCharacterLimit);
            expect(requestValue.length).toBeGreaterThan(visibleCharacterLimit);
            await expect(requestText).toHaveAttribute('data-bs-original-title', requestValue);
            await expect(requestText).toHaveText(`${requestValue.slice(0, visibleCharacterLimit)}...`);
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }
    });
});
