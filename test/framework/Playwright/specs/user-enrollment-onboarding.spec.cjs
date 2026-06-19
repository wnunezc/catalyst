const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@user-enrollment-onboarding Privileged user enrollment', () => {
    test('enrollment no longer exposes manual password fields and shows onboarding status', async ({ page }) => {
        try {
            await openSurface(page, expect, '/users/enroll', {
                signal: /Enroll|Usuario|User/,
                requireTriggers: false,
            });
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        await expect(page.locator('input[name="password"], input[name="password_confirm"]')).toHaveCount(0);
        await expect(page.locator('[name="onboarding"]')).toBeVisible();
    });
});
