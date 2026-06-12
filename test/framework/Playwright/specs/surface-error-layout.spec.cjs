const { test, expect } = require('../helpers/playwright.cjs');

test.describe('@surface-error Error surface layout', () => {
    test('404 renders through the canonical document and runtime', async ({ page }) => {
        const response = await page.goto('/missing-catalyst-surface', { waitUntil: 'domcontentloaded' });

        expect(response?.status()).toBe(404);
        await expect(page.locator('body.catalyst-error-shell-body')).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
        await expect(page.locator('.catalyst-error-card')).toBeVisible();
        await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);
        await expect(page.locator('script[src*="/assets/js/catalyst/modules/shell-dropdowns.js"]')).toHaveCount(0);
    });
});
