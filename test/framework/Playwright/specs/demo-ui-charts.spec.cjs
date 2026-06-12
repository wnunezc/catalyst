const { test, expect } = require('../helpers/playwright.cjs');
const { chartPages } = require('../fixtures/demo-ui-catalog.cjs');
const {
    expectResourceLoaded,
    openDemoUiSurface,
    runOrSkipForEnvironment,
} = require('../helpers/demo-ui.cjs');

test.describe('@demo-ui @demo-ui-charts Demo UI chart assets', () => {
    for (const entry of chartPages) {
        test(`${entry.path} loads its chart engine and individual page script`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDemoUiSurface(page, expect, entry);
                await expectResourceLoaded(page, expect, entry.vendorScript);
                await expectResourceLoaded(page, expect, entry.pageScript);

                for (const script of entry.extraScripts) {
                    await expectResourceLoaded(page, expect, script);
                }

                await expect(page.locator(entry.renderedSelector).first()).toBeVisible();
            });
        });
    }
});
