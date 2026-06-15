const { test } = require('../helpers/playwright.cjs');
const { createAuthPool } = require('../helpers/auth-pool.cjs');

test('@roadmap7-full @roadmap7-auth-pool prepares isolated authenticated worker sessions', async ({ browser }, testInfo) => {
    test.setTimeout(45000 * Math.max(2, require('../helpers/auth-pool.cjs').parallelWorkers));
    await createAuthPool(browser, testInfo.project.use.baseURL);
});
