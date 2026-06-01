<?php

declare(strict_types=1);

return static function (array $scope): array {
    $catalog = is_array($scope['catalog'] ?? null) ? $scope['catalog'] : [];
    return [
        'admin_header' => [
            'eyebrow' => __('catalogs.item_form_page.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? __('catalogs.item_form_page.create_title')),
            'description' => strtr(__('catalogs.item_form_page.hero_lede'), [':catalog' => (string) ($catalog['label'] ?? '')]),
            'actions' => [
                ['label' => __('catalogs.common.back'), 'href' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0), 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],
    ];
};
