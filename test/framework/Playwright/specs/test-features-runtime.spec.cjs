const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@test-features-runtime Test Features canonical runtime', () => {
    test('/test-features uses the shared document, shell and central runtime', async ({ page }) => {
        await page.addInitScript(() => {
            window.__catalystPageErrors = [];
            window.addEventListener('error', (event) => {
                window.__catalystPageErrors.push(event.message);
            });
        });

        try {
            await openSurface(page, expect, '/test-features', {
                signal: /Test Features|Características de prueba/,
            });
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }

            throw error;
        }

        await expect(page.locator('body.catalyst-shell-body')).toHaveAttribute(
            'data-catalyst-ui-runtime',
            'ready'
        );
        await expect(page.locator('body')).toHaveAttribute('data-surface-context', 'devtools');
        await expect(page.locator('body')).toHaveAttribute('data-surface-page', 'test-features');
        await expect(page.locator('.app-topbar')).toBeVisible();
        await expect(page.locator('.sidenav-menu')).toBeVisible();
        await expect(page.locator('main .card').first()).toBeVisible();
        await expect(page.locator('[data-page-header]')).toBeVisible();
        await expect(page.locator('#catalyst-status-bar')).toBeVisible();
        await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);
        await expect(page.locator('script[src*="/assets/js/work/devtools/script.js"]')).toHaveCount(1);
        await expect(page.locator('script[src*="/assets/js/catalyst/modules/shell-dropdowns.js"]')).toHaveCount(0);
        expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
    });
});
