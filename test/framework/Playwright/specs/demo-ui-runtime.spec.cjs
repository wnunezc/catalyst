const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@demo-ui-runtime Demo UI runtime entry', () => {
    test('boots without page errors through the canonical script stack', async ({ page }) => {
        await page.addInitScript(() => {
            window.__catalystPageErrors = [];
            window.addEventListener('error', (event) => {
                window.__catalystPageErrors.push({
                    message: event.message,
                    source: event.filename,
                    line: event.lineno,
                    column: event.colno,
                });
            });
        });

        try {
            await openSurface(page, expect, '/demo-ui/alerts', {
                requireTriggers: false,
            });
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        const errors = await page.evaluate(() => window.__catalystPageErrors);
        expect(errors).toEqual([]);
        await expect(page.locator('body[data-surface-context="demo-ui"]')).toHaveAttribute(
            'data-catalyst-ui-runtime',
            'ready'
        );
    });
});
