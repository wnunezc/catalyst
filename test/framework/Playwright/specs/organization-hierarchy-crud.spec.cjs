const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@organization-hierarchy-crud Organization hierarchy management', () => {
    test('hierarchy rows expose update forms and delete guards', async ({ page }) => {
        try {
            await openSurface(page, expect, '/users/organization-hierarchy', {
                signal: /Organization|Organizaci/,
                requireTriggers: false,
            });
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        await expect(page.locator('form[action="/users/organization-hierarchy/organizations"]').first()).toBeVisible();
        await expect(page.locator('form[id^="org-update-"], form[id^="scope-update-"], form[id^="level-update-"], form[id^="unit-update-"]').first()).toBeVisible();
        await expect(page.locator('form[action*="/delete"], .badge.text-bg-secondary').first()).toBeVisible();
    });
});
