<?php

declare(strict_types=1);

return [
    'description' => 'Personal user account center, account security and assisted recovery flows.',
    'routes' => [
        'web' => [
            '/account/profile',
            '/account/security',
            '/account/security/mfa',
            '/account/recovery',
            '/account/recovery/mfa',
            '/account/recovery/support',
            '/account/recovery/compromised',
            '/account/activity',
            '/admin/account-recovery',
            '/admin/account-recovery/{id}',
            '/account-recovery/start',
            '/account-recovery/mfa',
            '/account-recovery/mfa/{token}',
            '/account-recovery/support',
            '/account-recovery/compromised',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/account',
            '/account-recovery',
            '/admin/account-recovery',
        ],
    ],
    'route_guards' => [],
    'permissions' => [
        [
            'slug' => 'manage-account-recovery',
            'label' => __('account.module.manage_recovery_label'),
            'description' => __('account.module.manage_recovery_description'),
            'action' => 'manage',
            'resource' => 'account-recovery',
            'resources_any' => ['account-recovery'],
            'role_fallback_any' => ['admin'],
        ],
    ],
    'navigation' => [
        'admin' => [
            [
                'context' => 'account-recovery',
                'label' => __('account.module.admin_recovery_label'),
                'href' => '/admin/account-recovery',
                'icon' => 'ti ti-lifebuoy',
                'matches' => ['/admin/account-recovery'],
                'group' => 'security',
                'group_label' => 'Security',
                'group_order' => 35,
                'hint' => __('account.module.admin_recovery_hint'),
                'order' => 50,
                'visibility' => [
                    ['permissions_any' => ['manage-account-recovery']],
                ],
            ],
        ],
        'public' => [],
        'breadcrumbs' => [],
    ],
];
