const { test, expect } = require('../helpers/playwright.cjs');
const fs = require('node:fs');
const path = require('node:path');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const customKey = 'framework.playwright_mail_test';
const customAsset = 'playwright-mail-asset.png';

async function openMailSurface(page, path = '/workspaces/mail-templates') {
    await openSurface(page, expect, path, {
        signal: /Mail Templates|Plantillas de correo/,
        requireTriggers: false,
    });
}

async function removeCustomTemplate(page) {
    await page.goto(`/workspaces/mail-templates/${encodeURIComponent(customKey)}`, {
        waitUntil: 'domcontentloaded',
    });
    const deleteButton = page.locator(`form[action$="/${customKey}/delete"] button`);
    if (await deleteButton.count()) {
        await Promise.all([
            page.waitForResponse((response) => response.request().method() === 'POST'
                && response.url().endsWith(`/${customKey}/delete`)),
            deleteButton.click(),
        ]);
        await page.waitForURL('**/workspaces/mail-templates');
    }
}

async function removeCustomAsset(page) {
    await page.goto('/workspaces/mail-templates', { waitUntil: 'domcontentloaded' });
    const deleteButton = page.locator(`form[action$="/assets/${customAsset}/delete"] button`);
    if (await deleteButton.count()) {
        await Promise.all([
            page.waitForResponse((response) => response.request().method() === 'POST'
                && response.url().endsWith(`/assets/${customAsset}/delete`)),
            deleteButton.click(),
        ]);
        await page.waitForURL('**/workspaces/mail-templates');
    }
}

test.describe('@mail-template-manager Framework mail template manager', () => {
    test('navigation follows Locale Tools and managed template lifecycle is reversible', async ({ page }) => {
        try {
            await openMailSurface(page);
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        const navigationOrder = await page.locator('#sidenav-menu a[href]').evaluateAll((links) => (
            links.map((link) => link.getAttribute('href'))
        ));
        const localeIndex = navigationOrder.indexOf('/workspaces/locale-tools');
        const mailIndex = navigationOrder.indexOf('/workspaces/mail-templates');
        expect(localeIndex).toBeGreaterThanOrEqual(0);
        expect(mailIndex).toBe(localeIndex + 1);
        await expect(page.locator('[data-mail-template-row="users.enrollment_onboarding_verified"]')).toBeVisible();

        try {
            const logo = fs.readFileSync(path.resolve(
                __dirname,
                '../../../../public/assets/vendor/inspinia/images/logo-sm.png'
            ));
            await page.locator('input[name="asset"]').setInputFiles({
                name: customAsset,
                mimeType: 'image/png',
                buffer: logo,
            });
            await Promise.all([
                page.waitForResponse((response) => response.request().method() === 'POST'
                    && response.url().endsWith('/workspaces/mail-templates/assets')),
                page.locator('form[action="/workspaces/mail-templates/assets"] button').click(),
            ]);
            await expect(page.locator(`[data-mail-asset="${customAsset}"]`)).toBeVisible();

            await page.goto('/workspaces/mail-templates/create', { waitUntil: 'domcontentloaded' });
            await page.locator('input[name="key"]').fill(customKey);
            await page.locator('input[name="name"]').fill('Playwright mail template');
            await page.locator('input[name="translation_catalog"]').fill('mail_framework_playwright_test');
            await page.locator('input[name="translation_namespace"]').fill('framework.playwright_mail_test');
            await page.locator('textarea[name="required_placeholders_json"]').fill('["name","link"]');
            await page.locator('textarea[name="sample_payload_json"]').fill(
                '{"name":"Ada","link":"https://example.invalid/action"}'
            );
            await page.locator('textarea[name="html"]').fill(
                `<img src="{{ asset:${customAsset} }}" alt=""><h1>{{ t:framework.playwright_mail_test.heading }}</h1><a href="{{ link }}">Open</a>`
            );
            await page.locator('textarea[name="text"]').fill(
                '{{ t:framework.playwright_mail_test.heading }} {{ link }}'
            );
            await page.locator('textarea[name="catalog_json"]').fill(
                '{"framework":{"playwright_mail_test":{"subject":"Hello :name","heading":"Welcome :name"}}}'
            );
            await Promise.all([
                page.waitForResponse((response) => response.request().method() === 'POST'
                    && response.url().endsWith('/workspaces/mail-templates')),
                page.locator('form[action="/workspaces/mail-templates"] button[type="submit"]').click(),
            ]);
            await page.waitForURL(`**/workspaces/mail-templates/${customKey}`);

            await expect(page.locator('input[name="key"]')).toHaveValue(customKey);
            await Promise.all([
                page.waitForResponse((response) => response.request().method() === 'POST'
                    && response.url().endsWith(`/${customKey}/preview`)),
                page.locator(`form[action$="/${customKey}/preview"] button`).click(),
            ]);
            await expect(page.locator('iframe[sandbox]')).toBeVisible();
            await expect(page.locator('h3.h6')).toContainText('Hello Ada');
            expect(await page.locator('iframe[sandbox]').getAttribute('sandbox')).toBe('');

            await page.goto('/workspaces/mail-templates', { waitUntil: 'domcontentloaded' });
            await Promise.all([
                page.waitForResponse((response) => response.request().method() === 'POST'
                    && response.url().endsWith(`/assets/${customAsset}/delete`)),
                page.locator(`form[action$="/assets/${customAsset}/delete"] button`).click(),
            ]);
            await expect(page.locator(`[data-mail-asset="${customAsset}"]`)).toBeVisible();
        } finally {
            await removeCustomTemplate(page);
            await removeCustomAsset(page);
        }

        await expect(page.locator(`[data-mail-template-row="${customKey}"]`)).toHaveCount(0);
        await expect(page.locator(`[data-mail-asset="${customAsset}"]`)).toHaveCount(0);
    });

    test('@mail-template-enrollment system enrollment template can be customized and restored', async ({ page }) => {
        const key = 'users.enrollment_onboarding_verified';
        try {
            await openMailSurface(page, `/workspaces/mail-templates/${key}?locale=es`);
        } catch (error) {
            if (isEnvironmentInterrupted(error)) {
                test.skip(true, error.message);
                return;
            }
            throw error;
        }

        const save = page.locator(`form[action$="/${key}"] button[type="submit"]`);
        await Promise.all([
            page.waitForResponse((response) => response.request().method() === 'POST'
                && response.url().endsWith(`/${key}`)),
            save.click(),
        ]);
        await page.waitForURL(`**/workspaces/mail-templates/${key}`);
        await expect(page.locator(`form[action$="/${key}/restore"] button`)).toBeVisible();

        await Promise.all([
            page.waitForResponse((response) => response.request().method() === 'POST'
                && response.url().endsWith(`/${key}/restore`)),
            page.locator(`form[action$="/${key}/restore"] button`).click(),
        ]);
        await expect(page.locator(`form[action$="/${key}/restore"] button`)).toHaveCount(0);

        await openSurface(page, expect, '/users/enroll', {
            signal: /Enroll|Usuario|User/,
            requireTriggers: false,
        });
        await expect(page.locator('input[name="password"], input[name="password_confirm"]')).toHaveCount(0);
        expect(await page.evaluate(() => window.__catalystPageErrors || [])).toEqual([]);
    });
});
