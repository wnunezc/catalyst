const { test, expect } = require('../helpers/playwright.cjs');
const { isEnvironmentInterrupted } = require('../helpers/environment.cjs');
const { openSurface } = require('../helpers/surface.cjs');
const {
    assertNoModalResidue,
    closeActiveModal,
    openModalFromTrigger,
} = require('../helpers/modal.cjs');

const directTargets = [
    '#standard-modal',
    '#bs-example-modal-lg',
    '#bs-example-modal-sm',
    '#full-width-modal',
    '#scrollable-modal',
    '#top-modal',
    '#bottom-modal',
    '#centermodal',
    '#multiple-one',
    '#exampleModalToggle',
    '#fullscreeexampleModal',
    '#exampleModalFullscreenSm',
    '#exampleModalFullscreenMd',
    '#exampleModalFullscreenLg',
    '#exampleModalFullscreenXl',
    '#exampleModalFullscreenXxl',
    '#staticBackdrop',
];

const varyingRecipients = ['@mdo', '@fat', '@getbootstrap'];

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

async function openDemoUi(page) {
    return openSurface(page, expect, '/demo-ui/modals', {
        signal: /Modals|Modal/,
        triggerSelector: '[data-bs-toggle="modal"]',
    });
}

async function modalContentDiagnostics(modal) {
    return modal.evaluate((element) => {
        const dialog = element.querySelector('.modal-dialog');
        const content = element.querySelector('.modal-content');
        const snapshot = (node) => {
            if (!(node instanceof HTMLElement)) {
                return null;
            }

            const style = window.getComputedStyle(node);
            const rect = node.getBoundingClientRect();

            return {
                className: node.className,
                display: style.display,
                visibility: style.visibility,
                opacity: style.opacity,
                position: style.position,
                width: style.width,
                height: style.height,
                rect: {
                    x: rect.x,
                    y: rect.y,
                    width: rect.width,
                    height: rect.height,
                },
            };
        };

        return {
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight,
            },
            modal: snapshot(element),
            dialog: snapshot(dialog),
            content: snapshot(content),
        };
    });
}

test.describe('@modals @demo-ui-modals Demo UI modal surface', () => {
    test('modal trigger inventory matches the active Demo UI reference', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openDemoUi(page);

            const targets = await page.locator('[data-bs-toggle="modal"]')
                .evaluateAll((triggers) => triggers.map((trigger) =>
                    trigger.getAttribute('data-bs-target') || trigger.getAttribute('href')
                ).filter((target) => target?.startsWith('#')));

            expect(targets).toHaveLength(23);
            for (const target of [...directTargets, '#multiple-two', '#exampleModalToggle2', '#exampleModal']) {
                expect(targets, `${target} is missing a trigger`).toContain(target);
                await expect(page.locator(target)).toHaveCount(1);
            }
            expect(targets.filter((target) => target === '#exampleModal')).toHaveLength(3);
        });
    });

    for (const target of directTargets) {
        test(`${target} opens from its visible trigger and cleans up`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDemoUi(page);

                const trigger = page.locator(`[data-bs-target="${target}"]:visible, a[href="${target}"][data-bs-toggle="modal"]:visible`).first();
                await expect(trigger).toBeVisible();
                const modal = await openModalFromTrigger(page, expect, trigger);
                const diagnostics = await modalContentDiagnostics(modal);
                await expect(modal.locator('.modal-content'), JSON.stringify(diagnostics)).toBeVisible();
                await closeActiveModal(page, expect);
                await assertNoModalResidue(page, expect);
            });
        });
    }

    for (const recipient of varyingRecipients) {
        test(`#exampleModal receives varying content for ${recipient}`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDemoUi(page);

                const trigger = page.locator(`[data-bs-target="#exampleModal"][data-bs-whatever="${recipient}"]`);
                await expect(trigger).toBeVisible();
                const modal = await openModalFromTrigger(page, expect, trigger);
                await expect(modal.locator('#recipient-name')).toHaveValue(recipient);
                await closeActiveModal(page, expect);
                await assertNoModalResidue(page, expect);
            });
        });
    }

    test('#multiple-one transitions to #multiple-two and cleans up', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openDemoUi(page);

            await openModalFromTrigger(page, expect, page.locator('[data-bs-target="#multiple-one"]:visible'));
            await openModalFromTrigger(page, expect, page.locator('#multiple-one [data-bs-target="#multiple-two"]'));
            await expect(page.locator('#multiple-one.show')).toHaveCount(0);
            await expect(page.locator('#multiple-two.show')).toBeVisible();
            await closeActiveModal(page, expect);
            await assertNoModalResidue(page, expect);
        });
    });

    test('#exampleModalToggle transitions to #exampleModalToggle2 and back', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openDemoUi(page);

            const first = await openModalFromTrigger(page, expect, page.locator('a[href="#exampleModalToggle"]:visible'));
            await expect(first.locator('.modal-content')).toBeVisible();
            const second = await openModalFromTrigger(page, expect, page.locator('#exampleModalToggle [data-bs-target="#exampleModalToggle2"]'));
            await expect(second.locator('.modal-content')).toBeVisible();
            await expect(page.locator('#exampleModalToggle2.show')).toBeVisible();
            const firstAgain = await openModalFromTrigger(page, expect, page.locator('#exampleModalToggle2 [data-bs-target="#exampleModalToggle"]'));
            await expect(firstAgain.locator('.modal-content')).toBeVisible();
            await expect(page.locator('#exampleModalToggle.show')).toBeVisible();
            await closeActiveModal(page, expect);
            await assertNoModalResidue(page, expect);
        });
    });
});
