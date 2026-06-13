const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');
const {
    assertModalLayering,
    assertNoModalResidue,
    closeActiveModal,
    openModalFromTrigger,
} = require('../helpers/modal.cjs');

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

async function openDevTools(page) {
    const surface = await openSurface(page, expect, '/test-features', {
        signal: /Test Features|Características de prueba|Modal/,
    });

    const runtimeRoot = page.locator('body');
    const overlay = page.locator('[data-catalyst-activity-overlay]');
    await expect(runtimeRoot).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
    await page.waitForLoadState('networkidle');
    await expect(overlay).toHaveAttribute('data-activity-state', 'idle');
    await expect(runtimeRoot).not.toHaveAttribute('aria-busy', 'true');

    return surface;
}

test.describe('@modals @devtools-modals DevTools modal surface', () => {
    for (const action of ['confirm-demo', 'alert-demo']) {
        test(`${action} opens and cleans up`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDevTools(page);

                const trigger = page.locator(`[data-devtools-action="${action}"]`);
                await expect(trigger).toBeVisible();
                await openModalFromTrigger(page, expect, trigger);
                await closeActiveModal(page, expect);
                await assertNoModalResidue(page, expect);
            });
        });
    }

    for (const path of ['/test-features/modal/sample-content', '/test-features/modal/form-content']) {
        test(`${path} loads into an independent modal`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDevTools(page);

                const trigger = page.locator(`[data-catalyst-modal-action="load"][data-modal-url="${path}"]`);
                await expect(trigger).toBeVisible();
                const responsePromise = page.waitForResponse((response) =>
                    response.url().includes(path) && response.status() === 200
                );
                const modal = await openModalFromTrigger(page, expect, trigger);
                await responsePromise;
                await expect(modal.locator('.modal-body')).not.toContainText('Loading...', { timeout: 15000 });
                await expect(modal.locator('.modal-body')).not.toBeEmpty();
                await closeActiveModal(page, expect);
                await assertNoModalResidue(page, expect);
            });
        });
    }

    test('API notification response opens and cleans up its modal', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openDevTools(page);

            const path = '/test-features/api/modal-trigger';
            const trigger = page.locator(`[data-devtools-action="api-call"][data-url="${path}"]`);
            await expect(trigger).toBeVisible();
            const responsePromise = page.waitForResponse((response) =>
                response.url().includes(path) && response.status() === 200
            );
            await trigger.click();
            await responsePromise;
            await expect(page.locator('.modal.show')).toBeVisible({ timeout: 10000 });
            await assertModalLayering(page, expect);
            await closeActiveModal(page, expect);
            await assertNoModalResidue(page, expect);
        });
    });

});
