<?php

declare(strict_types=1);

return static function (array $scope): array {
    $template = is_array($scope['template'] ?? null) ? $scope['template'] : null;
    $actions = [];
    if ($template !== null) {
        $actions[] = ['label' => __('documents.form_page.actions.view_detail'), 'href' => '/workspaces/document-templates/' . (int) ($template['id'] ?? 0), 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-eye'];
    }
    $actions[] = ['label' => __('documents.form_page.actions.back_list'), 'href' => '/workspaces/document-templates', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'];

    return [
        'page_header' => [
            'eyebrow' => __('documents.form_page.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? $scope['title'] ?? __('documents.form_page.create_title')),
            'description' => $template !== null ? __('documents.form_page.hero_lede_edit') : __('documents.form_page.hero_lede_create'),
            'actions' => $actions,
        ],
    ];
};
