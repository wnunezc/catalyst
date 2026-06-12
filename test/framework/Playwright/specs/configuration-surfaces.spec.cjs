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

const protectedSurfaces = [
    {
        name: 'Environment Setup',
        path: '/configuration/environment-setup',
        selector: '.settings-console',
    },
    {
        name: 'Application Health',
        path: '/configuration/application-health',
        selector: '.configuration-health-console',
    },
    {
        name: 'Platform Appearance',
        path: '/configuration/platform-appearance',
        selector: '[data-platform-appearance-form]',
    },
    {
        name: 'Feature Flags',
        path: '/configuration/feature-flags',
        selector: 'form[action^="/configuration/feature-flags"]',
    },
    {
        name: 'Plugins',
        path: '/configuration/plugins',
        selector: '.surface-page [data-datagrid="1"]',
    },
];

test.describe('@configuration-surfaces Configuration owner surfaces', () => {
    for (const surface of protectedSurfaces) {
        test(`${surface.name} renders through Configuration and the shared shell`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openSurface(page, expect, surface.path, { requireTriggers: false });

                await expect(page.locator(surface.selector).first()).toBeVisible();
                await expect(page.locator('.sidenav-menu')).toBeVisible();
                await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);
                await expect(page.locator('script[src*="/assets/js/work/configuration/script.js"]')).toHaveCount(1);
                await expect(page.locator('script[src*="/assets/js/work/operations/script.js"]')).toHaveCount(0);
            });
        });
    }

    for (const probe of ['live', 'ready']) {
        test(`public ${probe} probe exposes only the minimal contract`, async ({ request }) => {
            const response = await request.get(`/configuration/application-health/${probe}`);
            expect([200, 503]).toContain(response.status());

            const payload = await response.json();
            expect(Object.keys(payload).sort()).toEqual(['ok', 'status']);
            expect(typeof payload.ok).toBe('boolean');
            expect(typeof payload.status).toBe('string');
        });
    }
});
