const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@roles-edit-formbuilder Role edit FormBuilder regression', () => {
    test('role edit renders without development error when multiple-select values are arrays', async ({ page }) => {
        await page.addInitScript(() => {
            window.__catalystPageErrors = [];
            window.addEventListener('error', (event) => {
                window.__catalystPageErrors.push(event.message);
            });
        });

        try {
            await openSurface(page, expect, '/users/roles/1/edit', {
                signal: /Role|Rol/,
                requireTriggers: false,
            });
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        await expect(page.locator('form')).toBeVisible();
        await expect(page.locator('text=/Array to string conversion/i')).toHaveCount(0);
        expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
    });
});
