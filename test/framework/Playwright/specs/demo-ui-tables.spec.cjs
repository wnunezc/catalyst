const { test, expect } = require('../helpers/playwright.cjs');
const { tablePages } = require('../fixtures/demo-ui-catalog.cjs');
const {
    expectResourceLoaded,
    openDemoUiSurface,
    runOrSkipForEnvironment,
} = require('../helpers/demo-ui.cjs');

test.describe('@demo-ui @demo-ui-tables Demo UI table assets', () => {
    for (const entry of tablePages) {
        test(`${entry.path} loads its declared table assets`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDemoUiSurface(page, expect, entry);
                await expect(page.locator('table').first()).toBeVisible();

                for (const script of entry.scripts) {
                    await expectResourceLoaded(page, expect, script);
                }

                if (entry.pageScript) {
                    await expectResourceLoaded(page, expect, entry.pageScript);
                }

                if (entry.slug.startsWith('datatables/')) {
                    await expect(page.locator('.dt-container').first()).toBeVisible();
                }
            });
        });
    }
});
