<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('operations.plugins.title'),
            'title' => (string) ($scope['pageTitle'] ?? __('operations.plugins.title')),
            'description' => __('operations.plugins.hero_lede'),
        ],

        'grid' => (array) ($scope['pluginGrid'] ?? []),
    ];
};
