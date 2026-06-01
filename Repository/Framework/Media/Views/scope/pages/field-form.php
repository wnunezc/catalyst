<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('media.fields.form.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? $scope['title'] ?? __('media.fields.form.create_title')),
            'description' => __('media.fields.form.hero_lede'),
            'actions' => [
                ['label' => __('media.fields.form.actions.back'), 'href' => '/workspaces/media-fields', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],
    ];
};
