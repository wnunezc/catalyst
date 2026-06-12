const { isEnvironmentInterrupted } = require('./environment.cjs');
const { openSurface } = require('./surface.cjs');

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

async function openDemoUiSurface(page, expect, entry) {
    const result = await openSurface(page, expect, entry.path, {
        requireTriggers: false,
    });

    const root = page.locator('body[data-surface-context="demo-ui"]');
    await expect(root).toHaveAttribute('data-catalyst-inspinia-document', entry.doc);
    await expect(root).toHaveAttribute('data-catalyst-ui-runtime', 'ready');
    await expect(page.locator('.page-main-title').first()).toBeVisible();

    return { ...result, root };
}

async function expectResourceLoaded(page, expect, expectedUrl) {
    await expect.poll(async () => page.evaluate((url) => (
        performance.getEntriesByType('resource')
            .some((entry) => {
                const actual = new URL(entry.name, window.location.href);
                const expected = new URL(url, window.location.href);

                return actual.origin === expected.origin
                    && actual.pathname === expected.pathname;
            })
    ), expectedUrl)).toBe(true);
}

module.exports = {
    expectResourceLoaded,
    openDemoUiSurface,
    runOrSkipForEnvironment,
};
