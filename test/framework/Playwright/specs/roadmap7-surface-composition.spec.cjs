const { test, expect } = require('../helpers/playwright.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@roadmap7-surface-composition Native Bootstrap/Inspinia composition', () => {
    for (const path of [
        '/test-features',
        '/operations/audit-log',
        '/configuration/environment-setup',
        '/users',
        '/workspaces/catalogs',
        '/workspaces/document-templates',
        '/workspaces/media-library',
    ]) {
        test(`${path} keeps native card and table composition`, async ({ page }) => {
            await openSurface(page, expect, path, { requireTriggers: false });

            await expect(page.locator('[data-page-header]')).toHaveCount(1);
            await expect(page.locator('.section-card, .surface-section-card, .ui-surface-card, .operations-card')).toHaveCount(0);

            const orphanRegions = await page.locator('.card-header, .card-body, .card-footer').evaluateAll((regions) => (
                regions.filter((region) => !region.closest('.card')).length
            ));
            expect(orphanRegions).toBe(0);

            const forcedNoWrap = await page.locator('[data-datagrid] table.table-nowrap').count();
            expect(forcedNoWrap).toBe(0);
        });
    }
});
