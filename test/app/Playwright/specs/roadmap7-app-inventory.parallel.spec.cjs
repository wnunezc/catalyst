const { publicTest, expect } = require('../helpers/parallel-playwright.cjs');
const { inventory } = require('../../../framework/Playwright/fixtures/roadmap7-surface-inventory.cjs');

const routes = inventory.filter((route) => route.owner === 'app' && route.execution === 'parallel-readonly');

for (const route of routes) {
    publicTest(`@roadmap7-full ${route.pattern} renders through native composition`, async ({ page }) => {
        await page.addInitScript(() => {
            window.__catalystRoadmap7Errors = [];
            window.addEventListener('error', (event) => window.__catalystRoadmap7Errors.push(event.message));
        });

        const response = await page.goto(route.concretePath, { waitUntil: 'domcontentloaded' });
        expect(response).not.toBeNull();
        expect(response.status()).not.toBe(404);
        await expect(page.locator('html')).toHaveCount(1);
        await expect(page.locator('body')).toHaveCount(1);
        await expect(page.locator(
            '.section-card, .surface-section-card, .ui-surface-card, .operations-card, '
            + '.account-card, .auth-card, .datagrid-card, .settings-config-card'
        )).toHaveCount(0);

        const orphanRegions = await page.locator('.card-header, .card-body, .card-footer').evaluateAll((regions) => (
            regions.filter((region) => !region.closest('.card')).length
        ));
        expect(orphanRegions).toBe(0);
        expect(await page.evaluate(() => window.__catalystRoadmap7Errors)).toEqual([]);
    });
}
