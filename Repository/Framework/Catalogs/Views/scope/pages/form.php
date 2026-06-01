<?php

declare(strict_types=1);

return static function (array $scope): array {
    $catalog = is_array($scope['catalog'] ?? null) ? $scope['catalog'] : null;
    $actions = [];
    if ($catalog !== null) {
        $actions[] = ['label' => __('ui.actions.view'), 'href' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0), 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-eye'];
    }
    $actions[] = ['label' => __('catalogs.common.back'), 'href' => '/workspaces/catalogs', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'];

    return [
        'admin_header' => [
            'eyebrow' => __('catalogs.form_page.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? ($catalog !== null ? __('catalogs.form_page.edit_title') : __('catalogs.form_page.create_title'))),
            'description' => __('catalogs.form_page.hero_lede'),
            'actions' => $actions,
        ],
    ];
};
