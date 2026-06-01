<?php

declare(strict_types=1);

return static function (array $scope): array {
    $media = is_array($scope['media'] ?? null) ? $scope['media'] : null;
    return [
        'admin_header' => [
            'eyebrow' => __('media.library.index.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? $scope['title'] ?? __('media.library.form.create_title')),
            'description' => $media !== null ? __('media.library.form.hero_lede_edit') : __('media.library.form.hero_lede_create'),
            'actions' => [
                ['label' => __('media.library.form.hero_manage_fields'), 'href' => '/workspaces/media-fields', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-sliders'],
                ['label' => __('media.library.form.actions.back'), 'href' => '/workspaces/media-library', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],
    ];
};
