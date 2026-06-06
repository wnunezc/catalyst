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
    return openSurface(page, expect, '/test-features', {
        signal: /Test Features|Características de prueba|Modal/,
    });
}

test.describe('@modals @devtools-modals DevTools modal surface', () => {
    for (const action of ['confirm-demo', 'alert-demo']) {
        test(`${action} opens and cleans up`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDevTools(page);

                const trigger = page.locator(`[data-action="${action}"]`);
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

                const trigger = page.locator(`[data-action="load-modal"][data-url="${path}"]`);
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
            const trigger = page.locator(`[data-action="api-call"][data-url="${path}"]`);
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

    test('partial refresh wait modal remains layered until the request finishes', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openDevTools(page);

            const path = '/test-features/api/js-enhancements/partial-refresh';
            await page.route(`**${path}`, async (route) => {
                await new Promise((resolve) => setTimeout(resolve, 750));
                await route.continue();
            });

            const trigger = page.locator(`[data-action="partial-refresh"][data-url="${path}"]`);
            await expect(trigger).toBeVisible();
            const responsePromise = page.waitForResponse((response) =>
                response.url().includes(path) && response.status() === 200
            );
            await trigger.click();
            await expect(page.locator('#catalyst-wait-modal.show')).toBeVisible();
            await assertModalLayering(page, expect);
            await responsePromise;
            await expect(page.locator('#catalyst-wait-modal.show')).toHaveCount(0, { timeout: 10000 });
            await assertNoModalResidue(page, expect);
        });
    });
});
