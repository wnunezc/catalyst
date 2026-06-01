<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('media.fields.index.hero_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('media.fields.index.title')),
            'description' => __('media.fields.index.description'),
            'actions' => [
                ['label' => __('media.fields.index.hero_open_library'), 'href' => '/workspaces/media-library', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-photo-film'],
                ['label' => __('media.fields.form.actions.create'), 'href' => '/workspaces/media-fields/create', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-plus'],
            ],
        ],
    ];
};
