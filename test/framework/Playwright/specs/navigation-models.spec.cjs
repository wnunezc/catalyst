const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

async function runOrSkipForEnvironment(test, callback) {
    try {
        await callback();
    } catch (error) {
        if (isEnvironmentInterrupted(error)) {
            test.skip(true, error.message);
            return;
        }

        throw error;
    }
}

test.describe('@navigation-models Virtual navigation models', () => {
    test('Demo UI exposes recursive catalog navigation in the shared renderer', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openSurface(page, expect, '/demo-ui/charts/apex/line', { requireTriggers: false });

            await expect(page.locator('body[data-surface-context="demo-ui"]')).toHaveCount(1);
            await expect(page.locator('#sidenav-menu .sub-menu .sub-menu a[href="/demo-ui/charts/apex/line"]')).toHaveCount(1);
            await expect(page.locator('#sidenav-menu .side-nav-item.active a[href="/demo-ui/charts/apex/line"]')).toHaveCount(1);
        });
    });

    test('framework-admin keeps Configuration active and disconnected debt non-clickable', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openSurface(page, expect, '/configuration/application-health', { requireTriggers: false });

            await expect(page.locator('#sidenav-menu a[href="/configuration/application-health"]')).toHaveCount(1);
            const disconnectedDebt = page.locator('#sidenav-menu [aria-disabled="true"]', { hasText: 'Disconnected' });
            expect(await disconnectedDebt.count()).toBeGreaterThan(0);
            await expect(disconnectedDebt.first()).toHaveAttribute('aria-disabled', 'true');
            await expect(page.locator('#sidenav-menu a[href="/operations/deployments"]')).toHaveCount(0);
        });
    });

    test('application composes App dashboard and recursive Framework account capabilities', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openSurface(page, expect, '/account/profile', { requireTriggers: false });

            await expect(page.locator('#sidenav-menu a[href="/dashboard"]')).toHaveCount(1);
            await expect(page.locator('#sidenav-menu a[href="/account/profile"]')).toHaveCount(1);
            await expect(page.locator('#sidenav-menu .sub-menu a[href="/account/security/mfa"]')).toHaveCount(1);
            await expect(page.locator('#sidenav-menu .sub-menu a[href="/account/recovery/support"]')).toHaveCount(1);
        });
    });
});
