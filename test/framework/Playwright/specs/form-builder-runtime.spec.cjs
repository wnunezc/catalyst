const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@form-builder Neutral FormBuilder runtime', () => {
    test('real metadata form uses neutral dependencies and autosave', async ({ page }) => {
        try {
            await openSurface(page, expect, '/workspaces/media-fields/create', { requireTriggers: false });

            const form = page.locator('[data-form-builder]').first();
            await expect(form).toBeVisible();
            await expect(page.locator('[data-admin-form-builder], [data-admin-form-dependencies]')).toHaveCount(0);
            await expect(page.locator('link[href*="/assets/css/catalyst/form-builder.css"]')).toHaveCount(1);
            await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);

            const type = form.locator('[name="type"]');
            const selectDependency = form.locator('[data-depends-on="type"][data-depends-values~="select"]');
            await expect(type).toBeVisible();
            await type.selectOption('select');
            await expect(selectDependency).toBeVisible();
            await type.selectOption('text');
            await expect(selectDependency).toBeHidden();
            await expect(selectDependency.locator('input, select, textarea').first()).toBeDisabled();

            const autosaveKey = await form.getAttribute('data-form-autosave-key');
            const label = form.locator('[name="label"]');
            if (autosaveKey && await label.count()) {
                await label.fill('FormBuilder runtime probe');
                await expect.poll(() => page.evaluate((key) => (
                    localStorage.getItem(`catalyst:form-autosave:${key}`)
                ), autosaveKey)).toContain('FormBuilder runtime probe');
                await page.evaluate((key) => {
                    localStorage.removeItem(`catalyst:form-autosave:${key}`);
                }, autosaveKey);
            }
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }
    });
});
