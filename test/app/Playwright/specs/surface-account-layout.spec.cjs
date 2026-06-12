const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const accountSurfaces = ['/dashboard', '/account/profile', '/account/activity'];

async function openAccountSurface(page, path) {
    await page.addInitScript(() => {
        window.__catalystPageErrors = [];
        window.addEventListener('error', (event) => {
            window.__catalystPageErrors.push(event.message);
        });
    });

    await openSurface(page, expect, path, { requireTriggers: false });
    await expect(page.locator('body.catalyst-shell-body.account-page-body')).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
    if (path === '/dashboard' && await page.locator('.account-guest-shell').count() > 0) {
        await expect(page.locator('.account-guest-shell')).toBeVisible();
    } else {
        await expect(page.locator('.sidenav-menu')).toBeVisible();
        await expect(page.locator('.content-page')).toBeVisible();
    }
    await expect(page.locator('#catalyst-status-bar')).toBeVisible();
    await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);
    await expect(page.locator('script[src*="/assets/js/catalyst/modules/shell-dropdowns.js"]')).toHaveCount(0);
    expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
}

test.describe('@app-surface-account Account surface layout', () => {
    for (const path of accountSurfaces) {
        test(`${path} renders through the canonical document and runtime`, async ({ page }) => {
            try {
                await openAccountSurface(page, path);
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
