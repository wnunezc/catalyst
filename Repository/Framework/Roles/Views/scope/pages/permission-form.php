<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('roles.permissions.catalog_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.permissions.title')),
            'description' => __('roles.permission_form.hero_lede'),
            'actions' => [
                ['label' => __('roles.common.back_to_permissions'), 'href' => '/users/permissions', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],
    ];
};
