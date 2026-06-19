const expectedIncludedRouteCount = 120;

const routePatterns = [
    '/',
    '/account-recovery/compromised',
    '/account-recovery/mfa',
    '/account-recovery/mfa/{token}',
    '/account-recovery/start',
    '/account-recovery/support',
    '/account/activity',
    '/account/profile',
    '/account/recovery',
    '/account/recovery/compromised',
    '/account/recovery/mfa',
    '/account/recovery/support',
    '/account/security',
    '/account/security/mfa',
    '/api/v1/automation-rules',
    '/api/v1/automation-rules/{id}',
    '/api/v1/calendar/events',
    '/api/v1/catalog',
    '/api/v1/document-templates',
    '/api/v1/document-templates/{id}',
    '/api/v1/versions/{resourceKey}/{recordId}',
    '/api/v1/workflows',
    '/auth/social/callback/{provider}',
    '/auth/social/{provider}',
    '/configuration/application-health',
    '/configuration/application-health/live',
    '/configuration/application-health/ready',
    '/configuration/environment-setup',
    '/configuration/feature-flags',
    '/configuration/platform-appearance',
    '/configuration/plugins',
    '/dashboard',
    '/forgot-password',
    '/home',
    '/landing',
    '/login',
    '/mfa/challenge',
    '/mfa/setup',
    '/operations/api-management',
    '/operations/audit-log',
    '/operations/audit-log/{id}',
    '/operations/automation-rules',
    '/operations/automation-rules/create',
    '/operations/automation-rules/{id}',
    '/operations/automation-rules/{id}/edit',
    '/operations/deployments',
    '/operations/tenancy',
    '/register',
    '/reset-password/{token}',
    '/runtime/notifications',
    '/runtime/notifications/unread-count',
    '/runtime/websocket/token',
    '/store',
    '/test-features',
    '/test-features/api-response',
    '/test-features/api/js-enhancements/partial-refresh',
    '/test-features/api/modal-trigger',
    '/test-features/api/multiple-toasters',
    '/test-features/api/toaster-error',
    '/test-features/api/toaster-info',
    '/test-features/api/toaster-success',
    '/test-features/api/toaster-warning',
    '/test-features/cors-headers',
    '/test-features/db-connection',
    '/test-features/e-helper',
    '/test-features/flash/clear',
    '/test-features/flash/{type}',
    '/test-features/flash/{type}/persistent',
    '/test-features/i18n',
    '/test-features/infra',
    '/test-features/json',
    '/test-features/json-error',
    '/test-features/json-success',
    '/test-features/layout-test',
    '/test-features/logger-email',
    '/test-features/modal/form-content',
    '/test-features/modal/sample-content',
    '/test-features/orm/find-or-fail',
    '/test-features/orm/status',
    '/test-features/orm/user-demo',
    '/test-features/rbac-status',
    '/test-features/route-cache',
    '/test-features/validation-error',
    '/uml',
    '/users',
    '/users/account-recovery',
    '/users/account-recovery/{id}',
    '/users/enroll',
    '/users/organization-hierarchy',
    '/users/permissions',
    '/users/permissions/create',
    '/users/permissions/{id}/edit',
    '/users/roles',
    '/users/roles/create',
    '/users/roles/{id}/edit',
    '/users/roles/{id}/permissions',
    '/users/{userId}/roles',
    '/verify-email',
    '/verify-email/{token}',
    '/workspaces/catalogs',
    '/workspaces/catalogs/create',
    '/workspaces/catalogs/{id}',
    '/workspaces/catalogs/{id}/edit',
    '/workspaces/catalogs/{id}/items/create',
    '/workspaces/catalogs/{id}/items/{itemId}/edit',
    '/workspaces/document-templates',
    '/workspaces/document-templates/create',
    '/workspaces/document-templates/{id}',
    '/workspaces/document-templates/{id}/edit',
    '/workspaces/locale-tools',
    '/workspaces/mail-templates',
    '/workspaces/mail-templates/create',
    '/workspaces/mail-templates/{key}',
    '/workspaces/media-fields',
    '/workspaces/media-fields/create',
    '/workspaces/media-fields/{id}/edit',
    '/workspaces/media-library',
    '/workspaces/media-library/upload',
    '/workspaces/media-library/{id}/edit',
    '/workspaces/module-designer',
];

const appRoutes = new Set(['/', '/dashboard', '/home', '/landing', '/store']);

const guestRoutes = new Set([
    '/account-recovery/compromised',
    '/account-recovery/mfa',
    '/account-recovery/mfa/{token}',
    '/account-recovery/start',
    '/account-recovery/support',
    '/auth/social/callback/{provider}',
    '/auth/social/{provider}',
    '/forgot-password',
    '/login',
    '/mfa/challenge',
    '/mfa/setup',
    '/register',
    '/reset-password/{token}',
    '/verify-email',
    '/verify-email/{token}',
]);

const publicRoutes = new Set([
    '/',
    '/configuration/application-health/live',
    '/configuration/application-health/ready',
    '/dashboard',
    '/home',
    '/landing',
    '/store',
]);

const transportRoutes = new Set([
    '/configuration/application-health/live',
    '/configuration/application-health/ready',
    '/runtime/notifications',
    '/runtime/notifications/unread-count',
    '/runtime/websocket/token',
    '/test-features/api-response',
    '/test-features/api/js-enhancements/partial-refresh',
    '/test-features/api/modal-trigger',
    '/test-features/api/multiple-toasters',
    '/test-features/api/toaster-error',
    '/test-features/api/toaster-info',
    '/test-features/api/toaster-success',
    '/test-features/api/toaster-warning',
    '/test-features/cors-headers',
    '/test-features/db-connection',
    '/test-features/e-helper',
    '/test-features/json',
    '/test-features/json-error',
    '/test-features/json-success',
    '/test-features/logger-email',
    '/test-features/modal/form-content',
    '/test-features/modal/sample-content',
    '/test-features/orm/find-or-fail',
    '/test-features/orm/status',
    '/test-features/orm/user-demo',
    '/test-features/rbac-status',
    '/test-features/route-cache',
    '/test-features/validation-error',
]);

const flowRoutes = new Set([
    '/account-recovery/mfa/{token}',
    '/auth/social/callback/{provider}',
    '/auth/social/{provider}',
    '/configuration/environment-setup',
    '/mfa/challenge',
    '/mfa/setup',
    '/reset-password/{token}',
    '/test-features/flash/clear',
    '/test-features/flash/{type}',
    '/test-features/flash/{type}/persistent',
    '/verify-email/{token}',
]);

const serialRoutes = new Set([
    ...flowRoutes,
    '/test-features/logger-email',
]);

const discovery = {
    '/operations/audit-log/{id}': {
        from: '/operations/audit-log',
        selector: 'a[href^="/operations/audit-log/"]',
        pattern: /^\/operations\/audit-log\/[^/]+$/,
    },
    '/operations/automation-rules/{id}': {
        from: '/operations/automation-rules',
        selector: '[data-catalyst-href^="/operations/automation-rules/"], a[href^="/operations/automation-rules/"]',
        pattern: /^\/operations\/automation-rules\/\d+$/,
    },
    '/operations/automation-rules/{id}/edit': {
        from: '/operations/automation-rules',
        selector: '[data-catalyst-href^="/operations/automation-rules/"], a[href^="/operations/automation-rules/"]',
        pattern: /^\/operations\/automation-rules\/\d+\/edit$/,
    },
    '/users/account-recovery/{id}': {
        from: '/users/account-recovery',
        selector: 'a[href^="/users/account-recovery/"]',
        pattern: /^\/users\/account-recovery\/\d+$/,
    },
    '/users/permissions/{id}/edit': {
        from: '/users/permissions',
        selector: '[data-catalyst-href^="/users/permissions/"], a[href^="/users/permissions/"]',
        pattern: /^\/users\/permissions\/\d+\/edit$/,
    },
    '/users/roles/{id}/edit': {
        from: '/users/roles',
        selector: '[data-catalyst-href^="/users/roles/"], a[href^="/users/roles/"]',
        pattern: /^\/users\/roles\/\d+\/edit$/,
    },
    '/users/roles/{id}/permissions': {
        from: '/users/roles',
        selector: '[data-catalyst-href^="/users/roles/"], a[href^="/users/roles/"]',
        pattern: /^\/users\/roles\/\d+\/permissions$/,
    },
    '/users/{userId}/roles': {
        from: '/users',
        selector: '[data-catalyst-href^="/users/"], a[href^="/users/"]',
        pattern: /^\/users\/\d+\/roles$/,
    },
    '/workspaces/catalogs/{id}': {
        from: '/workspaces/catalogs',
        selector: '[data-catalyst-href^="/workspaces/catalogs/"], a[href^="/workspaces/catalogs/"]',
        pattern: /^\/workspaces\/catalogs\/\d+$/,
    },
    '/workspaces/catalogs/{id}/edit': {
        from: '/workspaces/catalogs',
        selector: '[data-catalyst-href^="/workspaces/catalogs/"], a[href^="/workspaces/catalogs/"]',
        pattern: /^\/workspaces\/catalogs\/\d+\/edit$/,
    },
    '/workspaces/catalogs/{id}/items/create': {
        from: '/workspaces/catalogs',
        selector: '[data-catalyst-href^="/workspaces/catalogs/"], a[href^="/workspaces/catalogs/"]',
        pattern: /^\/workspaces\/catalogs\/\d+$/,
        transform: (candidate) => `${candidate}/items/create`,
    },
    '/workspaces/catalogs/{id}/items/{itemId}/edit': {
        from: '/workspaces/catalogs',
        selector: '[data-catalyst-href^="/workspaces/catalogs/"], a[href^="/workspaces/catalogs/"]',
        pattern: /^\/workspaces\/catalogs\/\d+$/,
        follow: {
            selector: '[data-catalyst-href*="/items/"], a[href*="/items/"]',
            pattern: /^\/workspaces\/catalogs\/\d+\/items\/\d+\/edit$/,
        },
    },
    '/workspaces/document-templates/{id}': {
        from: '/workspaces/document-templates',
        selector: '[data-catalyst-href^="/workspaces/document-templates/"], a[href^="/workspaces/document-templates/"]',
        pattern: /^\/workspaces\/document-templates\/\d+$/,
    },
    '/workspaces/document-templates/{id}/edit': {
        from: '/workspaces/document-templates',
        selector: '[data-catalyst-href^="/workspaces/document-templates/"], a[href^="/workspaces/document-templates/"]',
        pattern: /^\/workspaces\/document-templates\/\d+\/edit$/,
    },
    '/workspaces/mail-templates/{key}': {
        from: '/workspaces/mail-templates',
        selector: 'a[href^="/workspaces/mail-templates/"]',
        pattern: /^\/workspaces\/mail-templates\/[a-z0-9._-]+$/,
    },
    '/workspaces/media-fields/{id}/edit': {
        from: '/workspaces/media-fields',
        selector: '[data-catalyst-href^="/workspaces/media-fields/"], a[href^="/workspaces/media-fields/"]',
        pattern: /^\/workspaces\/media-fields\/\d+\/edit$/,
    },
    '/workspaces/media-library/{id}/edit': {
        from: '/workspaces/media-library',
        selector: '[data-catalyst-href^="/workspaces/media-library/"], a[href^="/workspaces/media-library/"]',
        pattern: /^\/workspaces\/media-library\/\d+\/edit$/,
    },
};

const concretePaths = {
    '/account-recovery/mfa/{token}': '/account-recovery/mfa/roadmap7-invalid-token',
    '/api/v1/automation-rules/{id}': '/api/v1/automation-rules/0',
    '/api/v1/document-templates/{id}': '/api/v1/document-templates/0',
    '/api/v1/versions/{resourceKey}/{recordId}': '/api/v1/versions/roadmap7/0',
    '/auth/social/callback/{provider}': '/auth/social/callback/roadmap7',
    '/auth/social/{provider}': '/auth/social/roadmap7',
    '/reset-password/{token}': '/reset-password/roadmap7-invalid-token',
    '/test-features/flash/{type}': '/test-features/flash/info',
    '/test-features/flash/{type}/persistent': '/test-features/flash/info/persistent',
    '/verify-email/{token}': '/verify-email/roadmap7-invalid-token',
};

function classifyRoute(pattern) {
    const owner = appRoutes.has(pattern) ? 'app' : 'framework';
    const kind = flowRoutes.has(pattern)
        ? 'flow'
        : (pattern.startsWith('/api/') || transportRoutes.has(pattern) ? 'transport' : 'html');
    const execution = serialRoutes.has(pattern) ? 'serial-stateful' : 'parallel-readonly';
    const access = publicRoutes.has(pattern)
        ? 'public'
        : (guestRoutes.has(pattern) ? 'guest' : (pattern.startsWith('/api/v1/') ? 'api-token' : 'authenticated'));

    return {
        access,
        concretePath: concretePaths[pattern] || (pattern.includes('{') ? null : pattern),
        discovery: discovery[pattern] || null,
        execution,
        kind,
        owner,
        pattern,
    };
}

const inventory = routePatterns.map(classifyRoute);

function assertCompleteInventory(actualPatterns = routePatterns) {
    const unique = new Set(routePatterns);
    if (routePatterns.length !== expectedIncludedRouteCount || unique.size !== expectedIncludedRouteCount) {
        throw new Error(`ROADMAP-7 inventory must contain ${expectedIncludedRouteCount} unique routes.`);
    }

    const actual = [...actualPatterns].sort();
    const expected = [...routePatterns].sort();
    if (JSON.stringify(actual) !== JSON.stringify(expected)) {
        throw new Error('ROADMAP-7 Playwright inventory does not match the current included route list.');
    }

    for (const route of inventory) {
        if (!route.owner || !route.kind || !route.execution || (!route.concretePath && !route.discovery)) {
            throw new Error(`ROADMAP-7 route lacks an executable strategy: ${route.pattern}`);
        }
    }
}

module.exports = {
    assertCompleteInventory,
    expectedIncludedRouteCount,
    inventory,
    routePatterns,
};
