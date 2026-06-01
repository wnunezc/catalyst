<?php

declare(strict_types=1);

return [
    'key' => 'framework.business',
    'label' => 'Framework Business Modules',
    'version' => '1.0.0',
    'required' => false,
    'description' => 'Reusable business capabilities layered on metadata, media, documents, automation and API platform.',
    'modules' => [
        'framework.media',
        'framework.documents',
        'framework.automation',
        'framework.apiplatform',
    ],
];
