const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const skins = [
    { skin: 'default', theme: 'light', topbar: 'gray', sidenav: 'dark' },
    { skin: 'minimal', theme: 'light', topbar: 'gray', sidenav: 'dark' },
    { skin: 'modern', theme: 'light', topbar: 'gray', sidenav: 'dark' },
    { skin: 'material', theme: 'light', topbar: 'gray', sidenav: 'dark' },
    { skin: 'pixel', theme: 'light', topbar: 'gray', sidenav: 'dark' },
    { skin: 'luxe', theme: 'light', topbar: 'gray', sidenav: 'dark' },
    { skin: 'flat', theme: 'light', topbar: 'gray', sidenav: 'dark' },
    { skin: 'red-cross', theme: 'light', topbar: 'light', sidenav: 'light' },
    { skin: 'civil-protection', theme: 'light', topbar: 'dark', sidenav: 'light' },
    { skin: 'firefighters', theme: 'light', topbar: 'dark', sidenav: 'dark' },
    { skin: 'grempa', theme: 'dark', topbar: 'dark', sidenav: 'dark' },
];

test.describe('@theme-skins Shared document theme skins', () => {
    for (const entry of skins) {
        test(`${entry.skin} restores on the canonical document and shell`, async ({ page }) => {
            await page.addInitScript((config) => {
                window.localStorage.setItem('__THEME_CONFIG__', JSON.stringify({
                    skin: config.skin,
                    theme: config.theme,
                    'topbar-color': config.topbar,
                    'sidenav-color': config.sidenav,
                }));
            }, entry);

            try {
                await openSurface(page, expect, '/test-features', { requireTriggers: false });
            } catch (error) {
                if (isEnvironmentInterrupted(error)) {
                    test.skip(true, error.message);
                    return;
                }
                throw error;
            }

            const html = page.locator('html');
            const policy = await html.getAttribute('data-catalyst-customizer-policy');
            if (policy === 'locked') {
                const appearance = await page.evaluate(() => ({
                    effective: window.__CATALYST_APPEARANCE__?.effectiveConfig || {},
                    stored: JSON.parse(window.localStorage.getItem('__THEME_CONFIG__') || '{}'),
                }));

                expect(appearance.stored.skin).toBe(entry.skin);
                await expect(html).toHaveAttribute('data-skin', appearance.effective.skin);
                await expect(html).toHaveAttribute('data-bs-theme', appearance.effective.theme);
                await expect(html).toHaveAttribute('data-topbar-color', appearance.effective['topbar-color']);
                await expect(html).toHaveAttribute('data-menu-color', appearance.effective['sidenav-color']);
            } else {
                await expect(html).toHaveAttribute('data-skin', entry.skin);
                await expect(html).toHaveAttribute('data-bs-theme', entry.theme);
                await expect(html).toHaveAttribute('data-topbar-color', entry.topbar);
                await expect(html).toHaveAttribute('data-menu-color', entry.sidenav);
            }

            await expect(page.locator('body.catalyst-shell-body')).toHaveAttribute(
                'data-catalyst-ui-runtime',
                'ready'
            );
            await expect(page.locator('.wrapper > .app-topbar')).toHaveCount(1);
            await expect(page.locator('.wrapper > .sidenav-menu')).toHaveCount(1);
            await expect(page.locator('.wrapper > .content-page')).toHaveCount(1);
        });
    }
});
