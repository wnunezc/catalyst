<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('roles.roles.hero_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.roles.create_title')),
            'description' => __('roles.form.hero_lede'),
            'actions' => [
                ['label' => __('roles.common.back_to_roles'), 'href' => '/users/roles', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],
    ];
};
