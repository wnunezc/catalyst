const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const shellSurfaces = [
    '/audit-log',
    '/api-platform',
    '/automation-rules',
    '/configuration/application-health',
    '/configuration/feature-flags',
    '/configuration/platform-appearance',
    '/configuration/plugins',
    '/demo-ui/alerts',
    '/users',
    '/workspaces/catalogs',
    '/workspaces/document-templates',
    '/workspaces/media-library',
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
