<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

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
