const { test, expect } = require('../helpers/playwright.cjs');
const { openSurface } = require('../helpers/surface.cjs');

test.describe('@roadmap7-critical Critical visual contracts', () => {
    for (const path of [
        '/configuration/environment-setup',
        '/configuration/application-health',
        '/configuration/feature-flags',
        '/operations/api-management',
    ]) {
        test(`${path} renders metrics as native cards after the PageHeader`, async ({ page }) => {
            await openSurface(page, expect, path, { requireTriggers: false });
            const header = page.locator('[data-page-header]');
            const cards = page.locator('[data-page-header] ~ .row .card');
            await expect(header).toHaveCount(1);
            await expect(cards.first()).toBeVisible();
            await expect(header.locator('.page-header__metrics')).toHaveCount(0);
        });
    }

    test('/configuration/platform-appearance switches Bootstrap tabs', async ({ page }) => {
        await openSurface(page, expect, '/configuration/platform-appearance', { requireTriggers: false });
        await page.locator('#appearance-branding-tab').click();
        await expect(page.locator('#appearance-branding')).toHaveClass(/show/);
        await expect(page.locator('#appearance-branding')).toHaveClass(/active/);
    });

    test('/configuration/platform-appearance keeps global header and card tabs geometry', async ({ page }) => {
        await openSurface(page, expect, '/configuration/platform-appearance', { requireTriggers: false });

        for (const width of [1024, 1920, 3840, 7680]) {
            await page.setViewportSize({ width, height: 900 });

            const geometry = await page.evaluate(() => {
                const content = document.querySelector('.content-page').getBoundingClientRect();
                const header = document.querySelector('[data-page-header]').getBoundingClientRect();
                const headerMain = document.querySelector('[data-page-header] > .page-header__main').getBoundingClientRect();
                const form = document.querySelector('[data-platform-appearance-form]').getBoundingClientRect();
                const cardHeader = document.querySelector('.card-header:has(> .card-header-tabs)');
                const tabs = cardHeader.querySelector('.card-header-tabs');
                const activeTab = tabs.querySelector('.nav-link.active');
                const cardHeaderRect = cardHeader.getBoundingClientRect();
                const tabsRect = tabs.getBoundingClientRect();
                const activeTabRect = activeTab.getBoundingClientRect();
                const cardHeaderStyle = getComputedStyle(cardHeader);
                const tabsStyle = getComputedStyle(tabs);

                return {
                    headerLeftDelta: Math.abs(header.left - content.left),
                    headerRightDelta: Math.abs(header.right - content.right),
                    headerMainLeftDelta: Math.abs(headerMain.left - form.left),
                    headerMainRightDelta: Math.abs(headerMain.right - form.right),
                    formWidth: form.width,
                    cardHeaderDisplay: cardHeaderStyle.display,
                    cardHeaderBorderBottomWidth: cardHeaderStyle.borderBottomWidth,
                    cardHeaderBorderBottomStyle: cardHeaderStyle.borderBottomStyle,
                    tabsBorderBottomStyle: tabsStyle.borderBottomStyle,
                    tabsLeftInsetDelta: Math.abs(
                        (tabsRect.left - cardHeaderRect.left)
                        - (parseFloat(cardHeaderStyle.paddingLeft) / 2)
                    ),
                    tabsRightInsetDelta: Math.abs(
                        (cardHeaderRect.right - tabsRect.right)
                        - (parseFloat(cardHeaderStyle.paddingRight) / 2)
                    ),
                    activeBaselineDelta: Math.abs(activeTabRect.bottom - tabsRect.bottom),
                };
            });

            expect(geometry.headerLeftDelta).toBeLessThanOrEqual(0.5);
            expect(geometry.headerRightDelta).toBeLessThanOrEqual(0.5);
            expect(geometry.headerMainLeftDelta).toBeLessThanOrEqual(0.5);
            expect(geometry.headerMainRightDelta).toBeLessThanOrEqual(0.5);
            expect(geometry.formWidth).toBeLessThanOrEqual(1520.5);
            expect(geometry.cardHeaderDisplay).toBe('block');
            expect(geometry.cardHeaderBorderBottomWidth).toBe('0px');
            expect(geometry.cardHeaderBorderBottomStyle).toBe('none');
            expect(geometry.tabsBorderBottomStyle).toBe('solid');
            expect(geometry.tabsLeftInsetDelta).toBeLessThanOrEqual(0.5);
            expect(geometry.tabsRightInsetDelta).toBeLessThanOrEqual(0.5);
            expect(geometry.activeBaselineDelta).toBeLessThanOrEqual(0.5);
        }
    });

    test('/uml switches Bootstrap tabs and renders Mermaid with theme colors', async ({ page }) => {
        await openSurface(page, expect, '/uml', { requireTriggers: false });

        for (const slug of ['bootstrap', 'lifecycle', 'constants', 'config', 'middleware', 'layers']) {
            const button = page.locator(`#uml-tab-button-${slug}`);
            const panel = page.locator(`#uml-tab-${slug}`);

            await button.click();
            await expect(button).toHaveAttribute('aria-selected', 'true');
            await expect(panel).toHaveClass(/show/);
            await expect(panel).toHaveClass(/active/);

            for (const table of await panel.locator('table').all()) {
                await expect(table.locator('xpath=..')).toHaveClass(/table-responsive/);
                await expect(table).toBeVisible();
            }

            const diagram = panel.locator('.mermaid');
            if (await diagram.count()) {
                await expect(diagram.locator('svg')).toBeVisible({ timeout: 15000 });
                const legibility = await diagram.evaluate((element) => {
                    const svg = element.querySelector('svg');
                    const text = svg?.querySelector('text, .nodeLabel, .edgeLabel');
                    const wrapper = element.closest('.uml-diagram-wrap');

                    return {
                        fontSize: text ? Number.parseFloat(getComputedStyle(text).fontSize) : 0,
                        pageOverflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                        svgWidth: svg?.getBoundingClientRect().width ?? 0,
                        wrapperWidth: wrapper?.getBoundingClientRect().width ?? 0,
                    };
                });

                expect(legibility.fontSize).toBeGreaterThanOrEqual(16);
                expect(legibility.svgWidth).toBeGreaterThan(0);
                expect(legibility.wrapperWidth).toBeGreaterThan(0);
                expect(legibility.pageOverflow).toBeLessThanOrEqual(1);
            }

            const horizontalOverflow = await page.evaluate(
                () => document.documentElement.scrollWidth - document.documentElement.clientWidth
            );
            expect(horizontalOverflow).toBeLessThanOrEqual(1);
        }

        const activeDiagram = page.locator('.tab-pane.active .mermaid');
        const renderedBeforeThemeChange = await activeDiagram.locator('svg').innerHTML();
        await page.evaluate(() => {
            const root = document.documentElement;
            root.setAttribute('data-bs-theme', root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
        });
        await expect.poll(
            () => activeDiagram.locator('svg').innerHTML(),
            { timeout: 15000 }
        ).not.toBe(renderedBeforeThemeChange);
        await expect(activeDiagram.locator('.alert-danger')).toHaveCount(0);
        await expect(page.locator('.uml-nav-tab, .uml-tab-content')).toHaveCount(0);
    });

    test('/users/enroll constrains FormBuilder sections and names each responsibility', async ({ page }) => {
        await page.setViewportSize({ width: 2560, height: 1440 });
        await openSurface(page, expect, '/users/enroll', { requireTriggers: false });
        const formShell = page.locator('[data-form-builder="form"]');
        const card = formShell.locator('[data-form-builder-layout="grouped-card"]');
        const sections = card.locator('[data-form-builder-section]');
        const firstSectionFields = sections.first().locator('.col-md-6');

        await expect(formShell).toHaveCount(1);
        await expect(card).toHaveCount(1);
        await expect(sections).toHaveCount(3);
        await expect(firstSectionFields).toHaveCount(2);
        const titles = await sections.locator('h2').allTextContents();
        expect(titles.map((title) => title.trim())).toEqual([
            'Identity profile',
            'Credential policy',
            'Initial access',
        ]);
        expect(new Set(titles.map((title) => title.trim())).size).toBe(3);

        const [cardBox, firstFieldBox, secondFieldBox] = await Promise.all([
            card.boundingBox(),
            firstSectionFields.nth(0).boundingBox(),
            firstSectionFields.nth(1).boundingBox(),
        ]);

        expect(cardBox).not.toBeNull();
        expect(firstFieldBox).not.toBeNull();
        expect(secondFieldBox).not.toBeNull();
        expect(firstFieldBox.width).toBeLessThan(cardBox.width * 0.45);
        expect(secondFieldBox.width).toBeLessThan(cardBox.width * 0.45);
    });

    test('tables remain inset independently from DataGrid functionality', async ({ page }) => {
        test.setTimeout(90000);

        for (const path of [
            '/configuration/application-health',
            '/configuration/feature-flags',
            '/operations/api-management',
            '/users/account-recovery',
            '/workspaces/locale-tools?locale=en',
            '/workspaces/module-designer',
        ]) {
            await openSurface(page, expect, path, { requireTriggers: false });

            const geometry = await page.locator('table').evaluateAll((tables) => tables.map((table) => {
                const card = table.closest('.card');
                const body = table.closest('.card-body');
                const wrapper = table.closest('.table-responsive');
                const wrapperRect = wrapper?.getBoundingClientRect();
                const cardRect = card?.getBoundingClientRect();

                return {
                    hasCard: card !== null,
                    hasBody: body !== null,
                    hasWrapper: wrapper !== null,
                    bodyFlush: body?.classList.contains('p-0') ?? false,
                    leftInset: cardRect && wrapperRect ? wrapperRect.left - cardRect.left : 0,
                    rightInset: cardRect && wrapperRect ? cardRect.right - wrapperRect.right : 0,
                };
            }));

            expect(geometry.length).toBeGreaterThan(0);
            for (const table of geometry) {
                expect(table.hasCard).toBe(true);
                expect(table.hasBody).toBe(true);
                expect(table.hasWrapper).toBe(true);
                expect(table.bodyFlush).toBe(false);
                expect(table.leftInset).toBeGreaterThan(8);
                expect(table.rightInset).toBeGreaterThan(8);
            }
        }

        await openSurface(page, expect, '/operations/audit-log', { requireTriggers: false });
        const dataGridGeometry = await page.locator('[data-datagrid] table').evaluate((table) => {
            const card = table.closest('.card');
            const body = table.closest('.card-body');
            const wrapper = table.closest('.table-responsive');
            const cardRect = card.getBoundingClientRect();
            const wrapperRect = wrapper.getBoundingClientRect();

            return {
                bodyFlush: body.classList.contains('p-0'),
                leftDelta: Math.abs(wrapperRect.left - cardRect.left),
                rightDelta: Math.abs(wrapperRect.right - cardRect.right),
            };
        });

        expect(dataGridGeometry.bodyFlush).toBe(false);
        expect(dataGridGeometry.leftDelta).toBeGreaterThan(8);
        expect(dataGridGeometry.rightDelta).toBeGreaterThan(8);
    });

    test('/dashboard remains an application-owned minimal extension surface', async ({ page }) => {
        await openSurface(page, expect, '/account/profile', { requireTriggers: false });
        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/dashboard$/);
        await expect(page.locator('[data-account-page="dashboard"] .card')).toHaveCount(1);
    });

    test('organization hierarchy navigation exposes a rendered icon', async ({ page }) => {
        await openSurface(page, expect, '/users/organization-hierarchy', { requireTriggers: false });
        const link = page.locator('.sidenav-menu a[href="/users/organization-hierarchy"]');
        await expect(link.locator('i.ti-hierarchy-3')).toHaveCount(1);
    });
});
