const { test, expect } = require('../helpers/playwright.cjs');
const {
    openDemoUiSurface,
    runOrSkipForEnvironment,
} = require('../helpers/demo-ui.cjs');

const cases = [
    {
        name: 'accordion',
        path: '/demo-ui/accordions',
        doc: 'ui-accordions.html',
        trigger: '[data-bs-toggle="collapse"][data-bs-target="#collapseTwo"]:visible',
        targetAttribute: 'data-bs-target',
        visibleClass: 'show',
    },
    {
        name: 'collapse',
        path: '/demo-ui/collapse',
        doc: 'ui-collapse.html',
        trigger: '[data-bs-toggle="collapse"][href="#multiCollapseExample1"]:visible',
        targetAttribute: 'href',
        visibleClass: 'show',
    },
    {
        name: 'dropdown',
        path: '/demo-ui/dropdowns',
        doc: 'ui-dropdowns.html',
        trigger: '[data-bs-toggle="dropdown"]:visible',
        siblingTarget: '.dropdown-menu',
        visibleClass: 'show',
    },
    {
        name: 'offcanvas',
        path: '/demo-ui/offcanvas',
        doc: 'ui-offcanvas.html',
        trigger: '[data-bs-toggle="offcanvas"][data-bs-target="#offcanvasExample"]:visible',
        targetAttribute: 'data-bs-target',
        visibleClass: 'show',
        close: '[data-bs-dismiss="offcanvas"]:visible',
        residue: '.offcanvas-backdrop',
    },
    {
        name: 'popover',
        path: '/demo-ui/popovers',
        doc: 'ui-popovers.html',
        trigger: '[data-bs-toggle="popover"]:visible',
        overlay: '.popover',
        cleanup: 'toggle',
    },
    {
        name: 'tab',
        path: '/demo-ui/tabs',
        doc: 'ui-tabs.html',
        trigger: '[data-bs-toggle="tab"][href="#overview"]:visible',
        target: '#overview',
        visibleClass: 'active',
    },
    {
        name: 'tooltip',
        path: '/demo-ui/tooltips',
        doc: 'ui-tooltips.html',
        trigger: '[data-bs-toggle="tooltip"]:visible',
        overlay: '.tooltip',
        hover: true,
        cleanup: 'unhover',
    },
];

test.describe('@demo-ui @demo-ui-components Demo UI component behavior', () => {
    for (const definition of cases) {
        test(`${definition.name} initializes, responds and leaves no overlay residue`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDemoUiSurface(page, expect, definition);

                const trigger = page.locator(definition.trigger).first();
                await expect(trigger).toBeVisible();

                if (definition.hover) {
                    await trigger.hover();
                } else {
                    await trigger.click();
                }

                let target = null;
                if (definition.overlay) {
                    target = page.locator(definition.overlay).last();
                } else if (definition.target) {
                    target = page.locator(definition.target);
                } else if (definition.siblingTarget) {
                    target = trigger.locator(`xpath=following-sibling::*[contains(@class, "${definition.siblingTarget.slice(1)}")]`);
                } else {
                    const selector = await trigger.getAttribute(definition.targetAttribute);
                    expect(selector).toBeTruthy();
                    target = page.locator(selector);
                }

                await expect(target).toBeVisible();
                if (definition.visibleClass) {
                    await expect(target).toHaveClass(new RegExp(`\\b${definition.visibleClass}\\b`));
                }

                if (definition.close) {
                    await page.locator(definition.close).first().click();
                    await expect(target).toBeHidden();
                } else if (definition.cleanup === 'toggle') {
                    await trigger.click();
                    await expect(target).toBeHidden();
                } else if (definition.cleanup === 'unhover') {
                    await page.mouse.move(0, 0);
                    await expect(target).toBeHidden();
                } else {
                    await trigger.click();
                }

                if (definition.residue) {
                    await expect(page.locator(definition.residue)).toHaveCount(0);
                }
            });
        });
    }
});
