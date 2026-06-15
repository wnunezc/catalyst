const { test, expect } = require('../helpers/serial-playwright.cjs');
const { inventory } = require('../fixtures/roadmap7-surface-inventory.cjs');

const routes = inventory.filter((route) => route.owner === 'framework' && route.execution === 'serial-stateful');

test.describe.configure({ mode: 'serial' });

for (const route of routes) {
    test(`@roadmap7-full ${route.pattern} [serial ${route.kind}]`, async ({ page }) => {
        const response = await page.request.get(route.concretePath, { maxRedirects: 0 });
        expect(response.status(), route.pattern).not.toBe(404);
    });
}
