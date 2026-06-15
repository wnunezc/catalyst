const path = require('node:path');
const { parallelWorkers } = require('../../framework/Playwright/helpers/auth-pool.cjs');

const defaultEngineOutputDir = path.resolve(__dirname, '../../../../../../Engines/Playwright/test-results/catalyst-app');
const parallelInventory = /roadmap7-app-inventory\.parallel\.spec\.cjs/;
const parallelSpecs = [
    parallelInventory,
    /surface-public-layout\.spec\.cjs/,
];

module.exports = {
    testDir: path.join(__dirname, 'specs'),
    timeout: 45000,
    fullyParallel: false,
    forbidOnly: false,
    retries: 0,
    reporter: 'list',
    outputDir: process.env.CATALYST_PLAYWRIGHT_OUTPUT_DIR || defaultEngineOutputDir,
    projects: [
        {
            name: 'surface-parallel',
            testMatch: parallelSpecs,
            fullyParallel: true,
            workers: parallelWorkers,
        },
        {
            name: 'stateful-serial',
            testIgnore: parallelSpecs,
            fullyParallel: false,
            workers: 1,
        },
    ],
    use: {
        baseURL: process.env.CATALYST_E2E_BASE_URL || 'https://catalyst.dock',
        browserName: 'chromium',
        headless: true,
        ignoreHTTPSErrors: true,
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        video: 'off',
    },
};
