const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const representatives = [
    '/workspaces/catalogs',
    '/workspaces/module-designer',
    '/workspaces/media-fields',
    '/workspaces/media-library',
    '/workspaces/document-templates',
    '/workspaces/locale-tools',
    '/workspaces/mail-templates',
    '/operations/deployments',
    '/operations/tenancy',
    '/operations/audit-log',
    '/operations/api-management',
    '/operations/automation-rules',
];

test.describe('@canonical-owners Workspaces and Operations ownership', () => {
    for (const path of representatives) {
        test(`${path} is connected to the shared framework shell`, async ({ page }) => {
            try {
                await openSurface(page, expect, path, { requireTriggers: false });
                await expect(page.locator('body.catalyst-shell-body')).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
                await expect(page.locator('#sidenav-menu [aria-disabled="true"]', { hasText: 'Disconnected' })).toHaveCount(0);
                await expect(page.locator('#sidenav-menu a[href="/audit-log"]')).toHaveCount(0);
                await expect(page.locator('#sidenav-menu a[href="/api-platform"]')).toHaveCount(0);
                await expect(page.locator('#sidenav-menu a[href="/automation-rules"]')).toHaveCount(0);
            } catch (error) {
                if (isEnvironmentInterrupted(error)) {
                    test.skip(true, error.message);
                    return;
                }
                throw error;
            }
        });
    }

    test('/operations is not an overview route', async ({ page }) => {
        const response = await page.goto('/operations', { waitUntil: 'domcontentloaded' });
        expect(response).not.toBeNull();
        expect(response.status()).toBe(404);
    });
});
