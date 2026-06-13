const { test, expect } = require('../helpers/playwright.cjs');

const retiredRoutes = [
    '/api/public/home',
    '/api/public/landing',
    '/api/public/store',
    '/api/public/dashboard',
    '/api/notifications',
    '/api/presence/example/1/heartbeat',
    '/api/ws-token',
    '/index',
    '/index.php',
    '/test-features/ui-showcase',
    '/' + 'ad' + 'min/account-recovery',
];

const activeRoutes = [
    '/users',
    '/users/account-recovery',
    '/account/profile',
    '/runtime/notifications',
    '/runtime/notifications/unread-count',
    '/runtime/websocket/token',
    '/uml',
    '/demo-ui',
];

test.describe('@roadmap4-routes canonical route ownership', () => {
    for (const path of retiredRoutes) {
        test(`${path} is retired`, async ({ request }) => {
            const response = await request.get(path, { maxRedirects: 0 });
            expect(response.status()).toBe(404);
        });
    }

    for (const path of activeRoutes) {
        test(`${path} remains active`, async ({ request }) => {
            const response = await request.get(path, { maxRedirects: 0 });
            expect(response.status()).not.toBe(404);
        });
    }
});
