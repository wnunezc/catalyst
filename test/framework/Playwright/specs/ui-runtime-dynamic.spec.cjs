const { test, expect } = require('../helpers/playwright.cjs');
const { openDemoUiSurface, runOrSkipForEnvironment } = require('../helpers/demo-ui.cjs');
const { assertNoModalResidue, closeActiveModal, openModalFromTrigger } = require('../helpers/modal.cjs');

test.describe('@ui-runtime-dynamic Dynamic UI runtime', () => {
    test('initializes a dynamically inserted password toggle once', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openDemoUiSurface(page, expect, {
                path: '/demo-ui/alerts',
                doc: 'ui-alerts.html',
            });

            await page.evaluate(async () => {
                const fixture = document.createElement('section');
                fixture.id = 'dynamic-password-fixture';
                fixture.innerHTML = `
                    <div class="input-group">
                        <input id="dynamic-password" type="password" value="secret">
                        <button type="button" data-password-toggle aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                `;
                document.querySelector('.content-page .container-fluid, .content-page')
                    .appendChild(fixture);

                for (let scan = 0; scan < 2; scan += 1) {
                    await new Promise((resolve) => {
                        document.addEventListener('catalyst:ui:scanned', resolve, { once: true });
                        document.dispatchEvent(new CustomEvent('catalyst:dom:updated', {
                            detail: { target: fixture },
                        }));
                    });
                }
            });

            const input = page.locator('#dynamic-password');
            const toggle = page.locator('#dynamic-password-fixture [data-password-toggle]');
            await expect(input).toHaveAttribute('type', 'password');
            await toggle.click();
            await expect(input).toHaveAttribute('type', 'text');
            await expect(toggle).toHaveAttribute('aria-pressed', 'true');
            await toggle.click();
            await expect(input).toHaveAttribute('type', 'password');
        });
    });

    test('applies late registrations by DOM capability without duplicate mounts', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openDemoUiSurface(page, expect, {
                path: '/demo-ui/alerts',
                doc: 'ui-alerts.html',
            });

            const beforeInsert = await page.evaluate(async () => {
                const { registerUiComponent } = await import(
                    '/assets/js/catalyst/runtime/registration-queue.js'
                );
                const mounted = new WeakSet();
                window.__catalystLateMounts = 0;

                registerUiComponent({
                    name: 'test.late-registration',
                    phase: 'scan',
                    selector: '[data-test-late-registration]',
                    mount(root) {
                        const target = root.matches?.('[data-test-late-registration]')
                            ? root
                            : root.querySelector?.('[data-test-late-registration]');
                        if (!(target instanceof HTMLElement) || mounted.has(target)) {
                            return;
                        }

                        mounted.add(target);
                        window.__catalystLateMounts += 1;
                    },
                });

                await new Promise((resolve) => queueMicrotask(resolve));
                return window.__catalystLateMounts;
            });

            expect(beforeInsert).toBe(0);

            await page.evaluate(async () => {
                const fixture = document.createElement('section');
                fixture.dataset.testLateRegistration = 'true';
                document.querySelector('.content-page .container-fluid, .content-page')
                    .appendChild(fixture);

                for (let scan = 0; scan < 2; scan += 1) {
                    await new Promise((resolve) => {
                        document.addEventListener('catalyst:ui:scanned', resolve, { once: true });
                        document.dispatchEvent(new CustomEvent('catalyst:dom:updated', {
                            detail: { target: fixture },
                        }));
                    });
                }
            });

            await expect.poll(
                () => page.evaluate(() => window.__catalystLateMounts)
            ).toBe(1);
        });
    });

    test('scans a newly inserted modal and cleans up after interaction', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            await openDemoUiSurface(page, expect, {
                path: '/demo-ui/alerts',
                doc: 'ui-alerts.html',
            });

            await page.evaluate(() => new Promise((resolve) => {
                document.addEventListener('catalyst:ui:scanned', resolve, { once: true });
                const fragment = document.createElement('section');
                fragment.id = 'dynamic-runtime-fixture';
                fragment.className = 'card card-body mb-3';
                fragment.innerHTML = `
                    <button type="button" data-bs-toggle="modal" data-bs-target="#dynamic-runtime-modal">
                        Open dynamic modal
                    </button>
                    <div class="modal fade" id="dynamic-runtime-modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-body">Dynamic runtime content</div>
                                <button type="button" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                `;
                const content = document.querySelector('.content-page .container-fluid, .content-page');
                content.appendChild(fragment);
                document.dispatchEvent(new CustomEvent('catalyst:dom:updated', {
                    detail: { target: fragment },
                }));
            }));

            const trigger = page.getByRole('button', { name: 'Open dynamic modal' });
            const modal = await openModalFromTrigger(page, expect, trigger);
            await expect(modal).toContainText('Dynamic runtime content');
            await closeActiveModal(page, expect);
            await assertNoModalResidue(page, expect);
        });
    });
});
