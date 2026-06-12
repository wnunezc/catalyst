const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const authSurfaces = ['/login', '/forgot-password', '/verify-email'];

async function openAuthSurface(page, path) {
    await page.addInitScript(() => {
        window.__catalystPageErrors = [];
        window.addEventListener('error', (event) => {
            window.__catalystPageErrors.push(event.message);
        });
    });

    await openSurface(page, expect, path, { requireTriggers: false });
    await expect(page.locator('body.catalyst-auth-body')).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
    await expect(page.locator('.auth-layout-shell')).toBeVisible();
    await expect(page.locator('.auth-brand-panel')).toBeVisible();
    await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);
    await expect(page.locator('script[src*="/assets/js/catalyst/modules/shell-dropdowns.js"]')).toHaveCount(0);
    expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
}

test.describe('@surface-auth Auth surface layout', () => {
    for (const path of authSurfaces) {
        test(`${path} renders through the canonical document and runtime`, async ({ page }) => {
            try {
                await openAuthSurface(page, path);
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
