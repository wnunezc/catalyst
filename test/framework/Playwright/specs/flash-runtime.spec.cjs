const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

async function openTestFeatures(page) {
    try {
        await openSurface(page, expect, '/test-features', {
            signal: /Test Features|Características de prueba/,
        });
        await page.goto('/test-features/flash/clear', { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/test-features$/);
    } catch (error) {
        if (isEnvironmentInterrupted(error)) {
            test.skip(true, error.message);
            return false;
        }
        throw error;
    }

    return true;
}

test.describe('@flash-runtime SSR flash runtime', () => {
    test('delivers a one-shot flash through the central toaster', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        await page.locator(
            '[data-catalyst-href="/test-features/flash/success"]'
        ).click();
        await expect(page).toHaveURL(/\/test-features$/);
        await expect(page.locator('.catalyst-toast.toast-success')).toHaveCount(1);
        await expect(page.locator('#catalyst-ssr-state')).toHaveCount(1);
    });

    test('renders and dismisses a persistent flash through shared HTTP', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        await page.locator(
            '[data-catalyst-href="/test-features/flash/info/persistent"]'
        ).click();
        await expect(page).toHaveURL(/\/test-features$/);

        const alert = page.locator('[data-flash-id]').first();
        await expect(alert).toBeVisible();
        await alert.locator('[data-flash-dismiss]').click();
        await expect(alert).toHaveCount(0);
    });
});
