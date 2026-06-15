const { test, expect } = require('../helpers/playwright.cjs');
const { completeMfaChallenge, qaEmail, qaPassword } = require('../helpers/auth.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');

const protectedPath = '/account/profile';
const sessionCookieName = 'catalyst-session';
const rememberCookieName = 'catalyst_remember';
const protectedLoginUrl = /\/login\?redirect=(?:%2F|\/)account(?:%2F|\/)profile$/;

async function cookieByName(context, name) {
    return (await context.cookies()).find((cookie) => cookie.name === name);
}

test.describe('@auth-session User session lifecycle', () => {
    test('rejects invalid credentials and MFA routes without pending state', async ({ page, context }) => {
        await context.clearCookies();

        await page.goto('/mfa/challenge', { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/login$/);

        await page.goto('/mfa/setup', { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/login$/);

        await page.locator('input[name="email"]').fill(`missing-${Date.now()}@example.invalid`);
        await page.locator('input[name="password"]').fill('not-a-valid-password');
        await Promise.all([
            page.waitForURL(/\/login$/, { timeout: 15000 }),
            page.locator('form[action="/login"] button[type="submit"]').click(),
        ]);

        await expect(page.locator('input[name="email"]')).toBeVisible();
        await page.goto(protectedPath, { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(protectedLoginUrl);
    });

    test('login, MFA and logout rotate and invalidate the browser session', async ({ page, context, browser }) => {
        test.setTimeout(90000);

        try {
            await context.clearCookies();

            await page.goto(protectedPath, { waitUntil: 'domcontentloaded' });
            await expect(page).toHaveURL(protectedLoginUrl);
            await expect(page.locator('input[name="email"]')).toBeVisible();

            const anonymousCookie = await cookieByName(context, sessionCookieName);
            expect(anonymousCookie).toBeTruthy();
            expect(anonymousCookie.httpOnly).toBe(true);
            expect(anonymousCookie.secure).toBe(true);
            expect(anonymousCookie.sameSite).toBe('Strict');

            await page.locator('input[name="email"]').fill(qaEmail);
            await page.locator('input[name="password"]').fill(qaPassword);
            await page.locator('input[name="remember"]').check();

            await Promise.all([
                page.waitForURL(/\/mfa\/challenge$/, { timeout: 15000 }),
                page.locator('form[action="/login"] button[type="submit"]').click(),
            ]);

            const pendingMfaCookie = await cookieByName(context, sessionCookieName);
            expect(pendingMfaCookie).toBeTruthy();
            expect(pendingMfaCookie.value).not.toBe(anonymousCookie.value);

            await page.locator('input[name="code"]').fill('000000');
            await Promise.all([
                page.waitForResponse((response) => {
                    return response.request().method() === 'POST'
                        && new URL(response.url()).pathname === '/mfa/verify';
                }),
                page.locator('form[action="/mfa/verify"] button[type="submit"]').click(),
            ]);
            await expect(page).toHaveURL(/\/mfa\/challenge$/);
            await expect(page.locator('input[name="code"]')).toBeVisible();

            const mfaResponsePromise = page.waitForResponse((response) => {
                return response.request().method() === 'POST'
                    && new URL(response.url()).pathname === '/mfa/verify';
            });
            await completeMfaChallenge(page, protectedPath);
            await expect(page).toHaveURL(new RegExp(`${protectedPath}$`));

            expect((await mfaResponsePromise).status()).toBe(200);
            await expect(page.locator('[data-catalyst-activity-overlay]')).toHaveAttribute('data-activity-state', 'idle');
            await expect(page.locator('.catalyst-toast')).toHaveCount(1);

            const authenticatedCookie = await cookieByName(context, sessionCookieName);
            expect(authenticatedCookie).toBeTruthy();
            expect(authenticatedCookie.value).not.toBe(pendingMfaCookie.value);
            expect(await cookieByName(context, rememberCookieName)).toBeTruthy();

            await page.goto('/login', { waitUntil: 'domcontentloaded' });
            await expect(page).toHaveURL(/\/dashboard$/);
            await page.goto(protectedPath, { waitUntil: 'domcontentloaded' });
            await expect(page).toHaveURL(new RegExp(`${protectedPath}$`));

            const csrfResponse = await context.request.post('/logout', {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            expect(csrfResponse.status()).toBe(403);
            expect((await csrfResponse.json()).success).toBe(false);

            await page.reload({ waitUntil: 'domcontentloaded' });
            await expect(page).toHaveURL(new RegExp(`${protectedPath}$`));

            const accountToggle = page.locator('.catalyst-user-dropdown [data-bs-toggle="dropdown"]');
            await expect(accountToggle).toBeVisible();
            await accountToggle.click();

            const logoutButton = page.locator('.catalyst-user-dropdown form[action="/logout"] button[type="submit"]');
            await expect(logoutButton).toBeVisible();
            const logoutResponsePromise = page.waitForResponse((response) => {
                return response.request().method() === 'POST'
                    && new URL(response.url()).pathname === '/logout';
            });
            await Promise.all([
                page.waitForURL(protectedLoginUrl, { timeout: 15000 }),
                logoutButton.click(),
            ]);

            expect((await logoutResponsePromise).status()).toBe(200);
            await expect(page.locator('[data-catalyst-activity-overlay]')).toHaveAttribute('data-activity-state', 'idle');
            await expect(page.locator('.catalyst-toast')).toHaveCount(1);

            const loggedOutCookie = await cookieByName(context, sessionCookieName);
            expect(loggedOutCookie).toBeTruthy();
            expect(loggedOutCookie.value).not.toBe(authenticatedCookie.value);
            expect(await cookieByName(context, rememberCookieName)).toBeFalsy();

            await page.goto(protectedPath, { waitUntil: 'domcontentloaded' });
            await expect(page).toHaveURL(protectedLoginUrl);

            const staleContext = await browser.newContext({
                baseURL: test.info().project.use.baseURL,
                ignoreHTTPSErrors: true,
            });

            try {
                await staleContext.addCookies([authenticatedCookie]);
                const stalePage = await staleContext.newPage();
                await stalePage.goto(protectedPath, { waitUntil: 'domcontentloaded' });
                await expect(stalePage).toHaveURL(protectedLoginUrl);
            } finally {
                await staleContext.close();
            }
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }

            throw error;
        }
    });
});
