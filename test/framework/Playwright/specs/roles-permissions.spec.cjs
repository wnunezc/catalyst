const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@roles-permissions-card-geometry Role permission assignment cards', () => {
    test('permission checkboxes stay inside the bordered option card and label toggles the control', async ({ page }) => {
        await page.addInitScript(() => {
            window.__catalystPageErrors = [];
            window.addEventListener('error', (event) => {
                window.__catalystPageErrors.push(event.message);
            });
        });

        try {
            await openSurface(page, expect, '/users/roles/1/permissions', { requireTriggers: false });
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        const firstPermission = page.locator('form[action="/users/roles/1/permissions"] label').first();
        const firstCheckbox = firstPermission.locator('input[type="checkbox"][name="permissions[]"]');

        if (await firstCheckbox.count() === 0) {
            test.skip(true, 'No permission checkbox is available in the current fixture state.');
            return;
        }

        await expect(firstCheckbox).toBeVisible();

        const geometry = await firstPermission.evaluate((label) => {
            const card = label.querySelector('[data-role-permission-option]');
            const input = label.querySelector('input[type="checkbox"]');

            if (!(card instanceof HTMLElement) || !(input instanceof HTMLElement)) {
                return null;
            }

            const cardRect = card.getBoundingClientRect();
            const inputRect = input.getBoundingClientRect();

            return {
                inputLeftInside: inputRect.left >= cardRect.left,
                inputRightInside: inputRect.right <= cardRect.right,
                inputTopInside: inputRect.top >= cardRect.top,
                inputBottomInside: inputRect.bottom <= cardRect.bottom,
                hasOverflow: inputRect.left < cardRect.left
                    || inputRect.right > cardRect.right
                    || inputRect.top < cardRect.top
                    || inputRect.bottom > cardRect.bottom,
            };
        });

        expect(geometry).not.toBeNull();
        expect(geometry).toMatchObject({
            inputLeftInside: true,
            inputRightInside: true,
            inputTopInside: true,
            inputBottomInside: true,
            hasOverflow: false,
        });

        const wasChecked = await firstCheckbox.isChecked();
        await firstPermission.click({ position: { x: 80, y: 24 } });
        await expect(firstCheckbox).toBeChecked({ checked: !wasChecked });

        await firstPermission.click({ position: { x: 80, y: 24 } });
        await expect(firstCheckbox).toBeChecked({ checked: wasChecked });
        expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
    });
});
