const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

async function openTestFeatures(page) {
    try {
        await openSurface(page, expect, '/test-features', {
            signal: /Test Features|Características de prueba/,
        });

        const runtimeRoot = page.locator('body');
        const overlay = page.locator('[data-catalyst-activity-overlay]');
        await expect(runtimeRoot).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
        await page.waitForLoadState('networkidle');
        await expect(overlay).toHaveCount(1);
        await expect(overlay).toHaveAttribute('data-activity-state', 'idle');
        await expect(overlay).toHaveAttribute('aria-hidden', 'true');
        await expect(runtimeRoot).not.toHaveAttribute('aria-busy', 'true');
    } catch (error) {
        if (isEnvironmentInterrupted(error)) {
            test.skip(true, error.message);
            return false;
        }

        throw error;
    }

    return true;
}

async function observeNextNavigationActivity(page) {
    await page.evaluate(() => {
        sessionStorage.removeItem('catalyst-activity-navigation-observation');
        document.addEventListener('catalyst:activity:start', (event) => {
            if (!['navigation', 'submit'].includes(event.detail?.type)) {
                return;
            }

            const overlay = document.querySelector('[data-catalyst-activity-overlay]');
            sessionStorage.setItem('catalyst-activity-navigation-observation', JSON.stringify({
                type: event.detail.type,
                state: overlay?.getAttribute('data-activity-state'),
                ariaHidden: overlay?.getAttribute('aria-hidden'),
            }));
        });
    });
}

async function readNavigationObservation(page) {
    return page.evaluate(() => JSON.parse(
        sessionStorage.getItem('catalyst-activity-navigation-observation')
    ));
}

test.describe('@activity-overlay Global activity overlay', () => {
    test('releases the single boot overlay after the runtime is ready', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        const overlay = page.locator('[data-catalyst-activity-overlay]');
        await expect(overlay).toHaveCount(1);
        await expect(overlay).toHaveAttribute('data-activity-state', 'idle');
        await expect(overlay).toHaveAttribute('aria-hidden', 'true');
    });

    test('blocks during the visible foreground diagnostic and releases after completion', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        const overlay = page.locator('[data-catalyst-activity-overlay]');
        const result = page.locator('[data-activity-diagnostic-message]');
        await page.locator('[data-devtools-action="activity-foreground"]').click();
        await expect(overlay).toHaveAttribute('data-activity-state', 'request');
        await expect(overlay).toHaveAttribute('aria-hidden', 'false');
        await expect(result).toContainText(/completed|completado/i);
        await expect(overlay).toHaveAttribute('data-activity-state', 'idle');
    });

    test('does not block during the visible background diagnostic', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        const overlay = page.locator('[data-catalyst-activity-overlay]');
        const result = page.locator('[data-activity-diagnostic-message]');
        await page.locator('[data-devtools-action="activity-background"]').click();
        await expect(overlay).toHaveAttribute('data-activity-state', 'idle');
        await expect(result).toContainText(/completed|completado/i);
        await expect(overlay).toHaveAttribute('data-activity-state', 'idle');
    });

    test('keeps blocking until every concurrent foreground request finishes', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        const overlay = page.locator('[data-catalyst-activity-overlay]');
        const result = page.locator('[data-activity-diagnostic-message]');
        await page.locator('[data-devtools-action="activity-concurrent"]').click();
        await expect(overlay).toHaveAttribute('data-activity-state', 'request');
        await expect(result).toContainText(/completed|completado/i, { timeout: 15000 });
        await expect(overlay).toHaveAttribute('data-activity-state', 'idle');
    });

    test('releases after the visible error diagnostic', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        const overlay = page.locator('[data-catalyst-activity-overlay]');
        const result = page.locator('[data-activity-diagnostic-message]');
        await page.locator('[data-devtools-action="activity-error"]').click();
        await expect(overlay).toHaveAttribute('data-activity-state', 'request');
        await expect(result).toContainText(/expected|esperado/i);
        await expect(overlay).toHaveAttribute('data-activity-state', 'idle');
    });

    test('blocks duplicate visible foreground activation', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        let matchingRequests = 0;
        page.on('request', (request) => {
            if (request.url().includes('activity_probe=success')) {
                matchingRequests += 1;
            }
        });

        const result = page.locator('[data-activity-diagnostic-message]');
        await page.locator('[data-devtools-action="activity-foreground"]').dblclick();
        await expect(result).toContainText(/completed|completado/i, { timeout: 15000 });
        expect(matchingRequests).toBe(1);
    });

    test('releases foreground activity before displaying its success toaster', async ({ page }) => {
        if (!await openTestFeatures(page)) {
            return;
        }

        await page.evaluate(() => {
            window.__catalystToastActivityState = null;
            new MutationObserver(() => {
                if (!document.querySelector('.catalyst-toast.toast-success')) {
                    return;
                }

                window.__catalystToastActivityState = document
                    .querySelector('[data-catalyst-activity-overlay]')
                    ?.getAttribute('data-activity-state');
            }).observe(document.body, { childList: true, subtree: true });
        });

        await page.locator('[data-devtools-action="partial-refresh"]').click();
        await expect(page.locator('.catalyst-toast.toast-success')).toHaveCount(1);
        expect(await page.evaluate(() => window.__catalystToastActivityState)).toBe('idle');
    });

    test('blocks the current document during internal navigation', async ({ page }) => {
        await page.route('**/uml', async (route) => {
            await new Promise((resolve) => setTimeout(resolve, 1000));
            await route.continue();
        });

        if (!await openTestFeatures(page)) {
            return;
        }

        await observeNextNavigationActivity(page);
        await page.locator('a[href="/uml"]').first().click();
        await expect(page).toHaveURL(/\/uml$/);
        expect(await readNavigationObservation(page)).toEqual({
            type: 'navigation',
            state: 'navigation',
            ariaHidden: 'false',
        });
    });

    test('blocks the current document during a valid native submit', async ({ page }) => {
        await page.route('**/uml', async (route) => {
            await new Promise((resolve) => setTimeout(resolve, 1000));
            await route.continue();
        });

        if (!await openTestFeatures(page)) {
            return;
        }

        await observeNextNavigationActivity(page);
        await page.locator('[data-activity-native-submit] button[type="submit"]').click();
        await expect(page).toHaveURL(/\/uml\??$/);
        expect(await readNavigationObservation(page)).toEqual({
            type: 'submit',
            state: 'submit',
            ariaHidden: 'false',
        });
    });
});
