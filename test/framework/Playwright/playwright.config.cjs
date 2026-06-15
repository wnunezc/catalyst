const path = require('node:path');
const { parallelWorkers } = require('./helpers/auth-pool.cjs');

const defaultEngineOutputDir = path.resolve(__dirname, '../../../../../../Engines/Playwright/test-results/catalyst');
const authSetup = /auth-pool\.setup\.cjs/;
const parallelInventory = /roadmap7-framework-inventory\.parallel\.spec\.cjs/;
const parallelSpecs = [
    parallelInventory,
    /demo-ui-.*\.spec\.cjs/,
    /roadmap4-routes\.spec\.cjs/,
    /surface-auth-layout\.spec\.cjs/,
    /surface-error-layout\.spec\.cjs/,
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
            name: 'auth-setup',
            testMatch: authSetup,
            workers: 1,
        },
        {
            name: 'surface-parallel',
            testMatch: parallelSpecs,
            fullyParallel: true,
            workers: parallelWorkers,
            dependencies: ['auth-setup'],
        },
        {
            name: 'stateful-serial',
            testIgnore: [authSetup, ...parallelSpecs],
            fullyParallel: false,
            workers: 1,
            dependencies: ['auth-setup'],
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
