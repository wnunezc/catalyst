const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

async function openTestFeatures(page) {
    try {
        await openSurface(page, expect, '/test-features', {
            signal: /Test Features|Características de prueba/,
        });
    } catch (error) {
        if (isEnvironmentInterrupted(error)) {
            test.skip(true, error.message);
            return false;
        }

        throw error;
    }

    return true;
}

test.describe('@test-features-actions DevTools focused actions', () => {
    test('uses the shared notification API for direct toasts', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        await page.locator('[data-devtools-action="toast"][data-type="success"]').click();
        await expect(page.locator('.catalyst-toast.toast-success')).toHaveCount(1);
    });

    test('uses shared HTTP response actions for partial refresh', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        const target = page.locator('#js-enhancements-target');
        await page.locator('[data-devtools-action="partial-refresh"]').click();
        await expect(target).toContainText(/server|servidor/i, { timeout: 15000 });
        await expect(page.locator('.catalyst-toast.toast-success')).toHaveCount(1, { timeout: 15000 });
    });
});
