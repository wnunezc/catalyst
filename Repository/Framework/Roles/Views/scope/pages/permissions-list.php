<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('roles.permissions.catalog_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.permissions.title')),
            'description' => __('roles.permissions.listing_description'),
            'actions' => [
                ['label' => __('roles.common.back_to_roles'), 'href' => '/users/roles', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
                ['label' => __('roles.permissions.new'), 'href' => '/users/permissions/create', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-plus'],
            ],
        ],
    ];
};
