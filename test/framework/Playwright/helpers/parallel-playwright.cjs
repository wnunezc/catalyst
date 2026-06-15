const fs = require('node:fs');
const base = require('./playwright.cjs');
const { EnvironmentInterruptedError } = require('./environment.cjs');
const { authStatePath, parallelWorkers } = require('./auth-pool.cjs');

const test = base.test.extend({
    context: async ({ browser }, use, testInfo) => {
        const statePath = authStatePath(testInfo.parallelIndex % parallelWorkers);
        if (!fs.existsSync(statePath)) {
            throw new EnvironmentInterruptedError(`Authenticated ROADMAP-7 worker state is missing: ${statePath}`);
        }

        const context = await browser.newContext({
            baseURL: testInfo.project.use.baseURL,
            ignoreHTTPSErrors: true,
            storageState: statePath,
        });

        await use(context);
        await context.close();
    },
});

module.exports = {
    ...base,
    publicTest: base.test,
    test,
};
