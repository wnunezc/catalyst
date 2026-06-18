const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const shellSurfaces = [
    '/operations/audit-log',
    '/operations/api-management',
    '/operations/automation-rules',
    '/operations/deployments',
    '/operations/tenancy',
    '/configuration/application-health',
    '/configuration/feature-flags',
    '/configuration/platform-appearance',
    '/configuration/plugins',
    '/demo-ui/alerts',
    '/users',
    '/workspaces/catalogs',
    '/workspaces/document-templates',
    '/workspaces/locale-tools',
    '/workspaces/media-library',
    '/workspaces/module-designer',
    '/uml',
];

async function openShellSurface(page, path) {
    await page.addInitScript(() => {
        window.__catalystPageErrors = [];
        window.addEventListener('error', (event) => {
            window.__catalystPageErrors.push(event.message);
        });
    });

    await openSurface(page, expect, path, { requireTriggers: false });
    await expect(page.locator('body.catalyst-shell-body')).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
    await expect(page.locator('.app-topbar')).toBeVisible();
    await expect(page.locator('.sidenav-menu')).toBeVisible();
    await expect(page.locator('#catalyst-status-bar')).toBeVisible();
    await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);
    await expect(page.locator('script[src*="/assets/js/catalyst/modules/shell-dropdowns.js"]')).toHaveCount(0);
    const scrollOwnership = await page.evaluate(async () => {
        const content = document.querySelector('.content-page');
        const contentScroller = content?.querySelector('.simplebar-content-wrapper');
        const initialWindowScrollY = window.scrollY;

        window.scrollTo(0, initialWindowScrollY === 0 ? 1 : 0);
        await new Promise((resolve) => window.requestAnimationFrame(resolve));
        const windowScrollMoved = window.scrollY !== initialWindowScrollY;
        window.scrollTo(0, initialWindowScrollY);

        let contentHasOverflow = false;
        let contentScrollMoved = false;

        if (contentScroller instanceof HTMLElement) {
            const initialContentScrollTop = contentScroller.scrollTop;

            contentHasOverflow = contentScroller.scrollHeight > contentScroller.clientHeight;
            if (contentHasOverflow) {
                contentScroller.scrollTop = initialContentScrollTop === 0 ? 1 : 0;
                await new Promise((resolve) => window.requestAnimationFrame(resolve));
                contentScrollMoved = contentScroller.scrollTop !== initialContentScrollTop;
                contentScroller.scrollTop = initialContentScrollTop;
            }
        }

        return {
            documentScrollable: document.documentElement.scrollHeight > document.documentElement.clientHeight,
            windowScrollMoved,
            hasContentScroller: contentScroller instanceof HTMLElement,
            contentHasOverflow,
            contentScrollMoved,
        };
    });
    expect(scrollOwnership.documentScrollable, JSON.stringify(scrollOwnership)).toBe(false);
    expect(scrollOwnership.windowScrollMoved, JSON.stringify(scrollOwnership)).toBe(false);
    expect(scrollOwnership.hasContentScroller, JSON.stringify(scrollOwnership)).toBe(true);
    if (scrollOwnership.contentHasOverflow) {
        expect(scrollOwnership.contentScrollMoved, JSON.stringify(scrollOwnership)).toBe(true);
    }
    expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
}

test.describe('@shell-layout Application shell layout', () => {
    for (const path of shellSurfaces) {
        test(`${path} renders through the canonical document and runtime`, async ({ page }) => {
            try {
                await openShellSurface(page, path);
            } catch (error) {
                if (isEnvironmentInterrupted(error)) {
                    test.skip(true, error.message);
                    return;
                }
                throw error;
            }
        });
    }
});

test.describe('@shell-mobile-sidebar Mobile shell sidebar', () => {
    test('tablet runtime closes the sidebar and supports toggle, swipe and backdrop dismissal', async ({ page }) => {
        await page.setViewportSize({ width: 800, height: 1280 });
        await page.addInitScript(() => {
            window.__catalystPageErrors = [];
            window.addEventListener('error', (event) => {
                window.__catalystPageErrors.push(event.message);
            });
        });
        await openSurface(page, expect, '/configuration/application-health', { requireTriggers: false });
        await expect(page.locator('body.catalyst-shell-body')).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
        await expect(page.locator('.app-topbar')).toBeVisible();
        await expect(page.locator('#catalyst-status-bar')).toBeVisible();

        const html = page.locator('html');
        const sidebar = page.locator('.sidenav-menu');
        const toggle = page.locator('[data-shell-sidebar-toggle]:visible');
        const backdrop = page.locator('.catalyst-shell-sidebar-backdrop');
        const activityOverlay = page.locator('[data-catalyst-activity-overlay]');

        await expect(toggle).toHaveJSProperty('tagName', 'BUTTON');
        await expect(html).toHaveAttribute('data-sidenav-size', 'offcanvas');
        await expect(html).not.toHaveClass(/sidebar-enable/);
        await expect(sidebar).toHaveCSS('opacity', '0');
        expect(await sidebar.evaluate((element) => element.getBoundingClientRect().right)).toBeLessThanOrEqual(0);

        await toggle.click();
        await expect(html).toHaveClass(/sidebar-enable/);
        await expect(sidebar).toHaveCSS('opacity', '1');
        expect(await sidebar.evaluate((element) => element.getBoundingClientRect().left)).toBeGreaterThanOrEqual(0);
        await expect(backdrop).toBeVisible();
        await expect(activityOverlay).toHaveAttribute('data-activity-state', 'idle');

        await backdrop.click({ position: { x: 700, y: 100 } });
        await expect(html).not.toHaveClass(/sidebar-enable/);
        await expect(sidebar).toHaveCSS('opacity', '0');

        await page.dispatchEvent('body', 'pointerdown', {
            pointerId: 1,
            pointerType: 'touch',
            clientX: 4,
            clientY: 500,
            isPrimary: true,
        });
        await page.dispatchEvent('body', 'pointerup', {
            pointerId: 1,
            pointerType: 'touch',
            clientX: 180,
            clientY: 505,
            isPrimary: true,
        });
        await expect(html).toHaveClass(/sidebar-enable/);

        await page.dispatchEvent('body', 'pointerdown', {
            pointerId: 2,
            pointerType: 'touch',
            clientX: 250,
            clientY: 500,
            isPrimary: true,
        });
        await page.dispatchEvent('body', 'pointerup', {
            pointerId: 2,
            pointerType: 'touch',
            clientX: 40,
            clientY: 505,
            isPrimary: true,
        });
        await expect(html).not.toHaveClass(/sidebar-enable/);
        await expect(sidebar).toHaveCSS('opacity', '0');
        expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
    });

    test('desktop runtime keeps the sidebar fixed and visible', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
        await openShellSurface(page, '/configuration/application-health');

        await expect(page.locator('html')).toHaveAttribute('data-sidenav-size', 'default');
        await expect(page.locator('html')).not.toHaveClass(/sidebar-enable/);
        await expect(page.locator('.sidenav-menu')).toBeVisible();
    });
});

test.describe('@roles-edit-error-regression Roles edit error handling', () => {
    test('first available role edit surface does not fall into the bootstrap error fallback', async ({ page }) => {
        await openShellSurface(page, '/users/roles');

        const editLink = page.locator('a[href^="/users/roles/"][href$="/edit"]').first();

        if (await editLink.count() === 0) {
            test.skip(true, 'No role edit link is available in the current fixture state.');
            return;
        }

        await editLink.click();
        await page.waitForLoadState('domcontentloaded');

        await expect(page.locator('body')).not.toContainText('Error template is not found for: handler_error');
        await expect(page.locator('body')).not.toContainText('Allowed memory size');
        await expect(page.locator('form[action^="/users/roles/"]')).toBeVisible();
    });
});
