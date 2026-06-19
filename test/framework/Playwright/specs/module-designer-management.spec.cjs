const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@module-designer-management Module Designer management', () => {
    test('designer lists inspected modules with status and safe delete blocking', async ({ page }) => {
        try {
            await openSurface(page, expect, '/workspaces/module-designer', {
                signal: /Module Designer|Diseñador/,
                requireTriggers: false,
            });
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        await expect(page.locator('[data-module-designer-row]').first()).toBeVisible();
        await expect(page.locator('[data-module-designer-row] .badge').first()).toBeVisible();
        await expect(page.locator('form[action*="/workspaces/module-designer/modules/"][action$="/delete"], [data-module-designer-row] .text-bg-secondary').first()).toBeVisible();
    });
});
