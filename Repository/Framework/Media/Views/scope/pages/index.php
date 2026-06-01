<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('media.library.index.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('media.library.index.title')),
            'description' => __('media.library.index.hero_lede'),
            'actions' => [
                ['label' => __('media.library.index.hero_manage_fields'), 'href' => '/workspaces/media-fields', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-sliders'],
                ['label' => __('media.library.index.hero_upload_asset'), 'href' => '/workspaces/media-library/upload', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-upload'],
            ],
        ],
    ];
};
