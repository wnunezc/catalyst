<?php

declare(strict_types=1);

return [
    'description' => 'Authentication, recovery, MFA and social access surfaces.',
    'routes' => [
        'web' => [
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password/{token}',
            '/verify-email',
            '/verify-email/{token}',
            '/logout',
            '/auth/social/{provider}',
            '/auth/social/callback/{provider}',
            '/mfa/setup',
            '/mfa/challenge',
            '/mfa/enable',
            '/mfa/disable',
            '/mfa/verify',
        ],
        'api' => [],
        'aliases' => [],
        'prefixes' => [
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/verify-email',
            '/logout',
            '/auth/social',
            '/mfa',
        ],
    ],
    'feature_flags' => [
        'social_auth',
        'mfa',
    ],
    'seeds' => [
        'users',
        'remember_tokens',
        'email_verification_tokens',
        'password_reset_tokens',
    ],
];
