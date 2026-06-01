<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('roles.roles.hero_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.roles.title')),
            'description' => __('roles.roles.hero_lede'),
            'actions' => [
                ['label' => __('roles.roles.permissions_link'), 'href' => '/users/permissions', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-key'],
                ['label' => __('roles.roles.new'), 'href' => '/users/roles/create', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-plus'],
            ],
        ],
    ];
};
