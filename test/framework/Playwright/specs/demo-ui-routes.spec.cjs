const { test, expect } = require('../helpers/playwright.cjs');
const { allPages } = require('../fixtures/demo-ui-catalog.cjs');
const {
    openDemoUiSurface,
    runOrSkipForEnvironment,
} = require('../helpers/demo-ui.cjs');

test.describe('@demo-ui @demo-ui-routes Demo UI route inventory', () => {
    for (const entry of allPages) {
        test(`${entry.path} renders ${entry.doc} under the common runtime`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDemoUiSurface(page, expect, entry);
            });
        });
    }
});
