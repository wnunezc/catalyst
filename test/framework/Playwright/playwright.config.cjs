const path = require('node:path');

const defaultEngineOutputDir = path.resolve(__dirname, '../../../../../../Engines/Playwright/test-results/catalyst');

module.exports = {
    testDir: path.join(__dirname, 'specs'),
    timeout: 45000,
    fullyParallel: false,
    workers: 1,
    forbidOnly: false,
    retries: 0,
    reporter: 'list',
    outputDir: process.env.CATALYST_PLAYWRIGHT_OUTPUT_DIR || defaultEngineOutputDir,
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
