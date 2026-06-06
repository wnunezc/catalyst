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
});
