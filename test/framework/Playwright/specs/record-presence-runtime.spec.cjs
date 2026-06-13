const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@record-presence Global RecordPresence runtime', () => {
    test('real automation consumer mounts through the shared runtime', async ({ page }) => {
        try {
            await openSurface(page, expect, '/operations/automation-rules', { requireTriggers: false });

            const candidates = page.locator('[data-catalyst-href^="/operations/automation-rules/"]');
            let target = null;

            for (let index = 0; index < await candidates.count(); index++) {
                const candidate = candidates.nth(index);
                const href = await candidate.getAttribute('data-catalyst-href');
                if (/^\/operations\/automation-rules\/\d+(?:\/edit)?$/.test(href || '')) {
                    target = candidate;
                    break;
                }
            }

            if (!target) {
                test.skip(true, 'No automation record is available for the RecordPresence probe.');
                return;
            }

            const heartbeatRequests = [];
            page.on('request', (request) => {
                if (/\/api\/presence\/.+\/\d+\/heartbeat$/.test(new URL(request.url()).pathname)) {
                    heartbeatRequests.push(request.url());
                }
            });

            await target.click();
            await expect(page).toHaveURL(/\/operations\/automation-rules\/\d+(?:\/edit)?$/);

            const presence = page.locator('[data-record-presence]').first();
            await expect(presence).toBeVisible();
            await expect(presence).toHaveClass(/record-presence/);
            await expect(page.locator('link[href*="/assets/css/catalyst/record-presence.css"]')).toHaveCount(1);
            await expect(page.locator('script[src*="/assets/js/catalyst/runtime/ui-runtime.js"]')).toHaveCount(1);

            if (await presence.getAttribute('data-is-owner') === '1') {
                await expect.poll(() => heartbeatRequests.length).toBeGreaterThan(0);
            }
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }
    });
});
