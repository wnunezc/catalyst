const { EnvironmentInterruptedError } = require('./environment.cjs');
const { loginToProtectedSurface } = require('./auth.cjs');

async function visibleCount(locator) {
    const count = await locator.count();
    let visible = 0;

    for (let index = 0; index < count; index++) {
        if (await locator.nth(index).isVisible()) {
            visible++;
        }
    }

    return visible;
}

async function openSurface(page, expect, targetPath, options = {}) {
    const response = await page.goto(targetPath, { waitUntil: 'domcontentloaded' }).catch((error) => {
        throw new EnvironmentInterruptedError(`Unable to load ${targetPath}: ${error.message}`);
    });

    if (!response) {
        throw new EnvironmentInterruptedError(`No HTTP response received for ${targetPath}.`);
    }

    if (/\/login(?:\?|$)/.test(page.url())) {
        await expect(page.locator('input[name="email"]')).toBeVisible();
        await loginToProtectedSurface(page, targetPath);
    } else if (/\/mfa(?:\?|$)/.test(page.url())) {
        await loginToProtectedSurface(page, targetPath);
    }

    await expect(page).toHaveURL(new RegExp(`${targetPath.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}$`));

    if (options.title) {
        await expect(page).toHaveTitle(options.title);
    }

    if (options.signal) {
        await expect(page.locator('body')).toContainText(options.signal);
    }

    const triggerSelector = options.triggerSelector || '[data-bs-toggle="modal"], [data-action*="modal"], [data-action="confirm-demo"], [data-action="alert-demo"]';
    const triggers = page.locator(triggerSelector);
    const triggerCount = await visibleCount(triggers);

    if (options.requireTriggers !== false && triggerCount < 1) {
        throw new Error(`No visible modal triggers found on ${targetPath} using ${triggerSelector}.`);
    }

    return {
        response,
        triggerCount,
        triggers,
        url: page.url(),
    };
}

module.exports = {
    openSurface,
    visibleCount,
};
