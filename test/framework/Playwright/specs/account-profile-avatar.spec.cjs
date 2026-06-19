const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@account-profile-avatar Account profile photo', () => {
    test('profile exposes a guarded avatar upload form and current image', async ({ page }) => {
        await page.addInitScript(() => {
            window.__catalystPageErrors = [];
            window.addEventListener('error', (event) => {
                window.__catalystPageErrors.push(event.message);
            });
        });

        try {
            await openSurface(page, expect, '/account/profile', {
                signal: /Profile|Perfil/,
                requireTriggers: false,
            });
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        const form = page.locator('form[action="/account/profile/avatar"][enctype="multipart/form-data"]');
        await expect(form).toBeVisible();
        await expect(form.locator('input[type="file"][name="avatar"]')).toHaveAttribute('accept', /image\/jpeg/);
        await expect(page.locator('img[alt][src*="users/"], img[alt][src*="user-avatars/"]').first()).toBeVisible();
        expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
    });
});
