const fs = require('node:fs');
const path = require('node:path');
const { loginToProtectedSurface } = require('./auth.cjs');
const { resolveEngineRoot } = require('./playwright.cjs');

const configuredWorkers = Number.parseInt(process.env.CATALYST_E2E_PARALLEL_WORKERS || '4', 10);
const parallelWorkers = Number.isInteger(configuredWorkers) && configuredWorkers > 0 ? configuredWorkers : 4;

function authPoolDirectory() {
    return path.join(resolveEngineRoot(), '.auth', 'catalyst-roadmap7');
}

function authStatePath(index) {
    return path.join(authPoolDirectory(), `worker-${index}.json`);
}

async function createAuthPool(browser, baseURL) {
    fs.mkdirSync(authPoolDirectory(), { recursive: true });

    for (let index = 0; index < parallelWorkers; index++) {
        const context = await browser.newContext({
            baseURL,
            ignoreHTTPSErrors: true,
        });
        const page = await context.newPage();

        await page.goto('/account/profile', { waitUntil: 'domcontentloaded' });
        if (/\/login(?:\?|$)/.test(page.url())) {
            await loginToProtectedSurface(page, '/account/profile');
        }

        if (!/\/account\/profile$/.test(new URL(page.url()).pathname)) {
            await context.close();
            throw new Error(`Unable to prepare authenticated ROADMAP-7 worker ${index}.`);
        }

        await context.storageState({ path: authStatePath(index) });
        await context.close();
    }
}

module.exports = {
    authStatePath,
    createAuthPool,
    parallelWorkers,
};
