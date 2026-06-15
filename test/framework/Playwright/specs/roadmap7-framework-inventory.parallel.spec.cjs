const { execFileSync } = require('node:child_process');
const path = require('node:path');
const { test, publicTest, expect } = require('../helpers/parallel-playwright.cjs');
const { inventory, routePatterns, assertCompleteInventory } = require('../fixtures/roadmap7-surface-inventory.cjs');

const projectRoot = path.resolve(__dirname, '../../../..');
const routes = inventory.filter((route) => route.owner === 'framework' && route.execution === 'parallel-readonly');

function runtimeIncludedRoutes() {
    const output = execFileSync('php', ['public/cli.php', 'route:list', '--json'], {
        cwd: projectRoot,
        encoding: 'utf8',
        windowsHide: true,
    });

    return JSON.parse(output)
        .filter((route) => route.methods.some((method) => method === 'GET' || method === 'HEAD'))
        .map((route) => route.uri)
        .filter((uri) => uri !== '/demo-ui' && !uri.startsWith('/demo-ui/'));
}

async function discoverPath(page, route) {
    await page.goto(route.discovery.from, { waitUntil: 'domcontentloaded' });
    const candidates = await page.locator(route.discovery.selector).evaluateAll((elements) => elements.map((element) => (
        element.getAttribute('data-catalyst-href') || element.getAttribute('href') || ''
    )));
    const candidate = candidates.find((value) => route.discovery.pattern.test(value)) || null;

    if (!candidate) {
        return null;
    }

    if (route.discovery.follow) {
        await page.goto(candidate, { waitUntil: 'domcontentloaded' });
        const nestedCandidates = await page.locator(route.discovery.follow.selector).evaluateAll((elements) => elements.map((element) => (
            element.getAttribute('data-catalyst-href') || element.getAttribute('href') || ''
        )));
        return nestedCandidates.find((value) => route.discovery.follow.pattern.test(value)) || null;
    }

    return route.discovery.transform ? route.discovery.transform(candidate) : candidate;
}

async function assertNativeComposition(page) {
    await expect(page.locator('html')).toHaveCount(1);
    await expect(page.locator('body')).toHaveCount(1);
    await expect(page.locator(
        '.section-card, .surface-section-card, .ui-surface-card, .operations-card, '
        + '.account-card, .auth-card, .datagrid-card, .settings-config-card'
    )).toHaveCount(0);

    const orphanRegions = await page.locator('.card-header, .card-body, .card-footer').evaluateAll((regions) => (
        regions.filter((region) => !region.closest('.card')).length
    ));
    expect(orphanRegions).toBe(0);
    await expect(page.locator('[data-datagrid] table.table-nowrap')).toHaveCount(0);
}

publicTest('@roadmap7-full inventory matches all 117 included GET/HEAD routes', async () => {
    assertCompleteInventory(runtimeIncludedRoutes());
    expect(routePatterns).toHaveLength(117);
});

for (const route of routes) {
    const runner = route.access === 'public' || route.access === 'guest' ? publicTest : test;

    runner(`@roadmap7-full ${route.pattern} [${route.kind}]`, async ({ page }) => {
        let targetPath = route.concretePath;
        if (!targetPath && route.discovery) {
            targetPath = await discoverPath(page, route);
            if (!targetPath) {
                runner.skip(true, `No runtime record exposes ${route.pattern}.`);
                return;
            }
        }

        if (route.kind === 'transport') {
            const response = await page.request.get(targetPath, { maxRedirects: 0 });
            expect(response.status()).not.toBe(404);
            return;
        }

        await page.addInitScript(() => {
            window.__catalystRoadmap7Errors = [];
            window.addEventListener('error', (event) => window.__catalystRoadmap7Errors.push(event.message));
        });

        const response = await page.goto(targetPath, { waitUntil: 'domcontentloaded' });
        expect(response).not.toBeNull();
        expect(response.status()).not.toBe(404);

        if ((response.headers()['content-type'] || '').includes('text/html') && response.status() < 400) {
            await assertNativeComposition(page);
            expect(await page.evaluate(() => window.__catalystRoadmap7Errors)).toEqual([]);
        }
    });
}
