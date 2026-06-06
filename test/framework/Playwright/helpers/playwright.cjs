const path = require('node:path');
const { createRequire } = require('node:module');
const { requireExistingPath, resolveWorkspacePath } = require('./environment.cjs');

function resolveEngineRoot() {
    return process.env.CATALYST_PLAYWRIGHT_ENGINE || resolveWorkspacePath('Engines', 'Playwright');
}

function loadPlaywrightTest() {
    const engineRoot = requireExistingPath('Playwright engine', resolveEngineRoot());
    requireExistingPath('Playwright node_modules', path.join(engineRoot, 'node_modules'));
    const engineRequire = createRequire(path.join(engineRoot, 'package.json'));
    return engineRequire('playwright/test');
}

module.exports = {
    ...loadPlaywrightTest(),
    resolveEngineRoot,
};
