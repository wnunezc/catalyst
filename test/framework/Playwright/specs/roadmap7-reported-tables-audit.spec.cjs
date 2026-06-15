const { test, expect } = require('../helpers/playwright.cjs');
const { openSurface } = require('../helpers/surface.cjs');

const reportedTableRoutes = [
    '/configuration/platform-appearance',
    '/configuration/plugins',
    '/workspaces/catalogs',
    '/workspaces/module-designer',
    '/workspaces/locale-tools?locale=en',
    '/operations/deployments',
    '/operations/audit-log',
    '/operations/api-management',
    '/users',
    '/users/roles',
    '/users/permissions',
    '/users/account-recovery',
    '/uml',
];

test('@roadmap7-reported-tables audits every user-reported table route', async ({ page }) => {
    test.setTimeout(180000);

    const audit = [];

    for (const path of reportedTableRoutes) {
        await openSurface(page, expect, path, { requireTriggers: false });

        if (path === '/uml') {
            for (const slug of ['bootstrap', 'lifecycle', 'constants', 'config', 'middleware', 'layers']) {
                await page.locator(`#uml-tab-button-${slug}`).click();
            }
        }

        audit.push(await page.evaluate((requestedPath) => {
            const tables = Array.from(document.querySelectorAll('table'));

            return {
                path: requestedPath,
                finalPath: `${location.pathname}${location.search}`,
                horizontalOverflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                tables: tables.map((table) => {
                    const card = table.closest('.card');
                    const body = table.closest('.card-body');
                    const wrapper = table.closest('.table-responsive');
                    const cardRect = card?.getBoundingClientRect();
                    const wrapperRect = wrapper?.getBoundingClientRect();
                    const dataGrid = table.closest('[data-datagrid]') !== null;

                    return {
                        type: dataGrid ? 'datagrid' : 'classic',
                        visible: table.getClientRects().length > 0,
                        hasCard: card !== null,
                        hasBody: body !== null,
                        hasResponsiveWrapper: wrapper !== null,
                        bodyFlush: body?.classList.contains('p-0') ?? false,
                        leftInset: cardRect && wrapperRect ? wrapperRect.left - cardRect.left : null,
                        rightInset: cardRect && wrapperRect ? cardRect.right - wrapperRect.right : null,
                        classes: table.className,
                    };
                }),
            };
        }, path));
    }

    console.log(`ROADMAP7_REPORTED_TABLES_AUDIT=${JSON.stringify(audit)}`);

    expect(audit).toHaveLength(reportedTableRoutes.length);

    const byPath = Object.fromEntries(audit.map((route) => [route.path, route]));
    expect(byPath['/configuration/platform-appearance'].tables).toHaveLength(0);

    for (const path of [
        '/configuration/plugins',
        '/workspaces/catalogs',
        '/operations/deployments',
        '/operations/audit-log',
        '/users',
        '/users/roles',
        '/users/permissions',
    ]) {
        expect(byPath[path].tables).toHaveLength(1);
        expect(byPath[path].tables[0].type).toBe('datagrid');
        expect(byPath[path].tables[0].bodyFlush).toBe(false);
        expect(byPath[path].tables[0].leftInset).toBeGreaterThan(8);
        expect(byPath[path].tables[0].rightInset).toBeGreaterThan(8);
    }

    for (const path of [
        '/workspaces/module-designer',
        '/workspaces/locale-tools?locale=en',
        '/operations/api-management',
        '/users/account-recovery',
    ]) {
        expect(byPath[path].tables.length).toBeGreaterThan(0);
        for (const table of byPath[path].tables) {
            expect(table.type).toBe('classic');
            expect(table.bodyFlush).toBe(false);
            expect(table.leftInset).toBeGreaterThan(8);
            expect(table.rightInset).toBeGreaterThan(8);
        }
    }

    expect(byPath['/uml'].tables).toHaveLength(4);
    for (const table of byPath['/uml'].tables) {
        expect(table.type).toBe('classic');
        expect(table.hasResponsiveWrapper).toBe(true);
    }

    for (const route of audit) {
        expect(route.horizontalOverflow).toBeLessThanOrEqual(1);
    }
});
