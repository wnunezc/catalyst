const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const publicSurfaces = ['/home', '/landing', '/store'];

async function openPublicSurface(page, path) {
    await page.addInitScript(() => {
        window.__catalystPageErrors = [];
        window.addEventListener('error', (event) => {
            window.__catalystPageErrors.push(event.message);
        });
    });

    await openSurface(page, expect, path, { requireTriggers: false });
    await expect(page.locator('body.catalyst-public-shell-body')).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
    await expect(page.locator('.catalyst-public-nav')).toBeVisible();
    await expect(page.locator('#catalyst-status-bar')).toBeVisible();
    await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);
    await expect(page.locator('script[src*="/assets/js/catalyst/modules/shell-dropdowns.js"]')).toHaveCount(0);
    expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
}

test.describe('@app-surface-public Public surface layout', () => {
    for (const path of publicSurfaces) {
        test(`${path} renders through the canonical document and runtime`, async ({ page }) => {
            try {
                await openPublicSurface(page, path);
            } catch (error) {
                if (isEnvironmentInterrupted(error)) {
                    test.skip(true, error.message);
                    return;
                }
                throw error;
            }
        });
    }
});
