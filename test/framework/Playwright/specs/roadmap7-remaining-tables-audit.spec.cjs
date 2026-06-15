const { test, expect } = require('../helpers/serial-playwright.cjs');
const { openSurface } = require('../helpers/surface.cjs');
const { inventory } = require('../fixtures/roadmap7-surface-inventory.cjs');

const directRoutes = [
    '/configuration/application-health',
    '/configuration/feature-flags',
    '/operations/automation-rules',
    '/test-features',
    '/test-features/layout-test',
    '/users/organization-hierarchy',
    '/workspaces/document-templates',
    '/workspaces/media-fields',
    '/workspaces/media-library',
];

const dynamicPatterns = [
    '/operations/automation-rules/{id}',
    '/users/{userId}/roles',
    '/workspaces/catalogs/{id}',
    '/workspaces/document-templates/{id}',
];

async function discoverPath(page, pattern) {
    const route = inventory.find((candidate) => candidate.pattern === pattern);
    if (!route?.discovery) {
        throw new Error(`Missing ROADMAP-7 discovery strategy for ${pattern}.`);
    }

    await openSurface(page, expect, route.discovery.from, { requireTriggers: false });
    const candidates = await page.locator(route.discovery.selector).evaluateAll((elements) => elements.map((element) => (
        element.getAttribute('data-catalyst-href') || element.getAttribute('href') || ''
    )));

    return candidates.find((value) => route.discovery.pattern.test(value)) || null;
}

async function auditCurrentSurface(page, label) {
    return page.evaluate((routeLabel) => ({
        path: routeLabel,
        finalPath: `${location.pathname}${location.search}`,
        horizontalOverflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        tables: Array.from(document.querySelectorAll('table')).map((table) => {
            const card = table.closest('.card');
            const body = table.closest('.card-body');
            const wrapper = table.closest('.table-responsive');
            const cardRect = card?.getBoundingClientRect();
            const wrapperRect = wrapper?.getBoundingClientRect();

            return {
                bodyFlush: body?.classList.contains('p-0') ?? false,
                hasBody: body !== null,
                hasCard: card !== null,
                hasResponsiveWrapper: wrapper !== null,
                leftInset: cardRect && wrapperRect ? wrapperRect.left - cardRect.left : null,
                rightInset: cardRect && wrapperRect ? cardRect.right - wrapperRect.right : null,
                type: table.closest('[data-datagrid]') ? 'datagrid' : 'classic',
                visible: table.getClientRects().length > 0,
            };
        }),
    }), label);
}

function assertTableComposition(route) {
    expect(route.horizontalOverflow, `${route.path} must not overflow horizontally.`).toBeLessThanOrEqual(1);

    for (const table of route.tables) {
        expect(table.hasCard, `${route.path} table must belong to a Bootstrap card.`).toBe(true);
        expect(table.hasBody, `${route.path} table must belong to a card body.`).toBe(true);
        expect(table.hasResponsiveWrapper, `${route.path} table must use table-responsive.`).toBe(true);
        expect(table.bodyFlush, `${route.path} table must not use flush card geometry.`).toBe(false);

        if (!table.visible) {
            continue;
        }

        expect(table.leftInset, `${route.path} table must preserve its left inset.`).toBeGreaterThan(8);
        expect(table.rightInset, `${route.path} table must preserve its right inset.`).toBeGreaterThan(8);
    }
}

test.describe.configure({ mode: 'serial' });

test('@roadmap7-remaining-tables audits every additional table surface', async ({ page }) => {
    test.setTimeout(240000);

    const audit = [];

    for (const path of directRoutes) {
        await openSurface(page, expect, path, { requireTriggers: false });
        audit.push(await auditCurrentSurface(page, path));
    }

    for (const pattern of dynamicPatterns) {
        const path = await discoverPath(page, pattern);
        if (!path) {
            audit.push({ path: pattern, skipped: 'No runtime record available.', tables: [] });
            continue;
        }

        await openSurface(page, expect, path, { requireTriggers: false });
        audit.push(await auditCurrentSurface(page, pattern));
    }

    console.log(`ROADMAP7_REMAINING_TABLES_AUDIT=${JSON.stringify(audit)}`);

    const testFeatures = audit.find((route) => route.path === '/test-features');
    expect(testFeatures.tables).toHaveLength(23);

    for (const route of audit.filter((candidate) => !candidate.skipped)) {
        assertTableComposition(route);
    }
});
