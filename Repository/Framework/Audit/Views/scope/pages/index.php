<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'admin_header' => [
            'eyebrow' => __('audit.index.title'),
            'title' => __('audit.index.title'),
            'description' => __('audit.index.hero_lede'),
        ],
    ];
};
