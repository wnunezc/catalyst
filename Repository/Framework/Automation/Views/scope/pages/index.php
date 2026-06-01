<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('automation.show.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('automation.index.title')),
            'description' => __('automation.index.hero_lede'),
            'actions' => [
                ['label' => __('automation.index.empty.action'), 'href' => '/automation-rules/create', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-plus'],
            ],
        ],
    ];
};
