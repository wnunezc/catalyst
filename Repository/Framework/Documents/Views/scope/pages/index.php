<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'page_header' => [
            'eyebrow' => __('documents.index.hero_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('documents.index.title')),
            'description' => __('documents.index.hero_lede'),
            'actions' => [
                ['label' => __('documents.form_page.actions.create'), 'href' => '/workspaces/document-templates/create', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-plus'],
            ],
        ],
    ];
};
