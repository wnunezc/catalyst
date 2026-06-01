<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('catalogs.index.hero_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('catalogs.index.title')),
            'description' => __('catalogs.index.hero_lede'),
            'actions' => [
                ['label' => __('catalogs.form_page.create_title'), 'href' => '/workspaces/catalogs/create', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-plus'],
            ],
        ],
    ];
};
