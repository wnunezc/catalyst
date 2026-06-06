const fs = require('node:fs');
const path = require('node:path');
const { EnvironmentInterruptedError, requireExistingPath, resolveWorkspacePath } = require('./environment.cjs');

function readEngineSecrets() {
    const engineRoot = process.env.CATALYST_PLAYWRIGHT_ENGINE || resolveWorkspacePath('Engines', 'Playwright');
    const secretsPath = process.env.CATALYST_E2E_SECRETS_FILE || path.join(engineRoot, '.secrets', 'catalyst.e2e.json');

    if (!fs.existsSync(secretsPath)) {
        return {};
    }

    try {
        return JSON.parse(fs.readFileSync(secretsPath, 'utf8'));
    } catch (error) {
        throw new EnvironmentInterruptedError(`Catalyst E2E secrets file is invalid: ${error.message}`);
    }
}

const engineSecrets = readEngineSecrets();
const qaEmail = process.env.CATALYST_E2E_EMAIL || engineSecrets.email || '';
const qaPassword = process.env.CATALYST_E2E_PASSWORD || engineSecrets.password || '';
const qaService = process.env.CATALYST_E2E_MFA_SERVICE || engineSecrets.mfaService || 'Catalyst Framework';

function loadMfaForge() {
    const mfaRoot = process.env.CATALYST_MFA_FORGE || resolveWorkspacePath('Engines', 'MFA-Forge');
    return require(path.join(requireExistingPath('MFA-Forge engine', mfaRoot), 'index.cjs'));
}

async function readQaTotp() {
    if (!qaEmail) {
        throw new EnvironmentInterruptedError('Catalyst E2E email is missing. Configure it in the local Playwright engine secrets file or CATALYST_E2E_EMAIL.');
    }

    const mfa = loadMfaForge();
    if (typeof mfa.readCurrentTotpFromMfaForgeAgent !== 'function') {
        throw new EnvironmentInterruptedError('MFA-Forge does not expose readCurrentTotpFromMfaForgeAgent(). Update the workspace MFA engine or configure a compatible replacement.');
    }

    let token;
    try {
        token = await mfa.readCurrentTotpFromMfaForgeAgent({
            user: qaEmail,
            serviceIncludes: qaService,
        });
    } catch (error) {
        throw new EnvironmentInterruptedError(`MFA-Forge could not provide a TOTP for the configured Catalyst E2E account: ${error.message}`);
    }

    if (!token) {
        throw new EnvironmentInterruptedError('MFA-Forge did not return a TOTP for the configured Catalyst E2E account.');
    }

    return token;
}

async function completeMfaChallenge(page, targetPath) {
    await page.waitForURL(/\/mfa(?:\/|\?|$)/, { timeout: 15000 });
    await page.locator('input[name="code"]').fill(await readQaTotp());
    await Promise.all([
        page.waitForURL(new RegExp(`${targetPath.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}$`), { timeout: 15000 }),
        page.locator('form button[type="submit"]').click(),
    ]);
}

async function loginToProtectedSurface(page, targetPath) {
    if (!qaEmail) {
        throw new EnvironmentInterruptedError('Catalyst E2E email is missing. Configure it in the local Playwright engine secrets file or CATALYST_E2E_EMAIL.');
    }

    if (!qaPassword) {
        throw new EnvironmentInterruptedError('Catalyst E2E password is missing. Configure it in the local Playwright engine secrets file or CATALYST_E2E_PASSWORD.');
    }

    await page.locator('input[name="email"]').fill(qaEmail);
    await page.locator('input[name="password"]').fill(qaPassword);

    const targetPattern = new RegExp(`${targetPath.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}$`);

    await Promise.all([
        page.waitForURL((url) => /\/mfa(?:\/|\?|$)/.test(url.pathname) || targetPattern.test(url.pathname), { timeout: 15000 }),
        page.locator('form button[type="submit"]').click(),
    ]);

    if (/\/mfa(?:\/|\?|$)/.test(page.url())) {
        await completeMfaChallenge(page, targetPath);
    }
}

module.exports = {
    completeMfaChallenge,
    loginToProtectedSurface,
    qaEmail,
    qaPassword,
    qaService,
};
