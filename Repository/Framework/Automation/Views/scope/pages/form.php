<?php

declare(strict_types=1);

return static function (array $scope): array {
    $rule = is_array($scope['rule'] ?? null) ? $scope['rule'] : null;
    $actions = [];
    if ($rule !== null) {
        $actions[] = ['label' => __('automation.form_page.actions.view_detail'), 'href' => '/automation-rules/' . (int) ($rule['id'] ?? 0), 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-eye'];
    }
    $actions[] = ['label' => __('automation.form_page.actions.back_list'), 'href' => '/automation-rules', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'];

    return [
        'admin_header' => [
            'eyebrow' => __('automation.form_page.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? $scope['title'] ?? __('automation.form_page.create_title')),
            'description' => $rule !== null ? __('automation.form_page.hero_lede_edit') : __('automation.form_page.hero_lede_create'),
            'actions' => $actions,
        ],
    ];
};
