<?php

declare(strict_types=1);

return [
    'key' => 'framework.core',
    'label' => 'Framework Core',
    'version' => '1.0.0',
    'required' => true,
    'description' => 'Mandatory operational core for auth, setup, RBAC and platform operations.',
    'modules' => [
        'framework.auth',
        'framework.settings',
        'framework.roles',
        'framework.audit',
        'framework.notification',
        'framework.operations',
    ],
];
