const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');
const {
    assertNoModalResidue,
    closeActiveModal,
    openModalFromTrigger,
} = require('../helpers/modal.cjs');

const activeSettingsModals = [
    'modal-app',
    'modal-db',
    'modal-mail',
    'modal-ftp',
    'modal-session',
    'modal-security',
    'modal-features',
    'modal-logging',
    'modal-websocket',
    'modal-devtools',
    'modal-cors',
];

async function runOrSkipForEnvironment(test, callback) {
    try {
        await callback();
    } catch (error) {
        if (isEnvironmentInterrupted(error)) {
            test.skip(true, error.message);
            return;
        }

        throw error;
    }
}

async function openSettings(page) {
    return openSurface(page, expect, '/configuration/environment-setup', {
        signal: /Framework Settings|Configuracion|Configuración/,
        triggerSelector: '[data-bs-toggle="modal"][data-bs-target^="#modal-"]',
    });
}

async function saveSettingsModal(page, modalId) {
    await page.addInitScript(() => {
        window.__catalystPageErrors = [];
        window.addEventListener('error', (event) => {
            window.__catalystPageErrors.push(event.message);
        });
    });

    await openSettings(page);

    const trigger = page.locator(`[data-bs-toggle="modal"][data-bs-target="#${modalId}"]`);
    const modal = await openModalFromTrigger(page, expect, trigger);
    const form = modal.locator('form[data-settings-modal-form]');
    const action = await form.getAttribute('action');
    const save = modal.locator('[data-settings-submit="save"]');

    await expect(save).toBeEnabled();

    const responsePromise = page.waitForResponse((response) => response.url().endsWith(action || '') && response.request().method() === 'POST');
    await save.click();
    const response = await responsePromise;
    const data = await response.json();

    expect(data.success).toBe(true);
    await expect(page.locator('[data-catalyst-activity-overlay]')).toHaveAttribute('data-activity-state', 'idle', { timeout: 10000 });
    await expect(page.locator(`#${modalId}.show`)).toHaveCount(0, { timeout: 10000 });
    await assertNoModalResidue(page, expect);

    const toast = page.locator('.catalyst-toast.toast-success').last();
    await expect(toast).toBeVisible({ timeout: 10000 });

    const layering = await page.evaluate(() => {
        const overlay = document.querySelector('[data-catalyst-activity-overlay]');
        const toast = document.querySelector('.catalyst-toast.toast-success');
        const toaster = document.querySelector('.catalyst-toaster-container');

        if (!(overlay instanceof HTMLElement) || !(toast instanceof HTMLElement) || !(toaster instanceof HTMLElement)) {
            return { ok: false, reason: 'Missing overlay, toaster or toast.' };
        }

        return {
            ok: overlay.dataset.activityState === 'idle'
                && Number.parseInt(window.getComputedStyle(toaster).zIndex || '0', 10)
                    > Number.parseInt(window.getComputedStyle(overlay).zIndex || '0', 10),
            overlayState: overlay.dataset.activityState,
            overlayZ: window.getComputedStyle(overlay).zIndex,
            toasterZ: window.getComputedStyle(toaster).zIndex,
        };
    });

    expect(layering.ok, JSON.stringify(layering)).toBe(true);
    expect(await page.evaluate(() => window.__catalystPageErrors)).toEqual([]);
}

test.describe('@modals @settings-modals Settings modal surface', () => {
    test('active modal inventory matches visible Settings triggers', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openSettings(page);

            const targets = await page.locator('[data-bs-toggle="modal"][data-bs-target^="#modal-"]:visible')
                .evaluateAll((triggers) => triggers.map((trigger) => trigger.getAttribute('data-bs-target')?.slice(1)).filter(Boolean));

            expect(targets.sort()).toEqual([...activeSettingsModals].sort());
            await expect(page.locator('#modal-cache')).toHaveCount(1);
            await expect(page.locator('[data-bs-target="#modal-cache"]:visible')).toHaveCount(0);
            await expect(page.locator('#modal-cache [data-settings-submit="save"]')).toBeDisabled();
        });
    });

    for (const modalId of activeSettingsModals) {
        test(`${modalId} opens from its visible card and cleans up`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openSettings(page);

                const trigger = page.locator(`[data-bs-toggle="modal"][data-bs-target="#${modalId}"]`);
                await expect(trigger).toBeVisible();
                const modal = await openModalFromTrigger(page, expect, trigger);
                await expect(modal.locator('.modal-title')).not.toBeEmpty();
                await expect(modal.locator('form[data-settings-modal-form]')).toHaveCount(1);
                await closeActiveModal(page, expect);
                await assertNoModalResidue(page, expect);
            });
        });
    }

    for (const modalId of ['modal-app', 'modal-security']) {
        test(`@settings-save-overlay ${modalId} save releases wait overlay and leaves toaster visible`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await saveSettingsModal(page, modalId);
            });
        });
    }
});
