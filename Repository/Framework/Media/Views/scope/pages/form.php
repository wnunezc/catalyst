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
    $media = is_array($scope['media'] ?? null) ? $scope['media'] : null;
    return [
        'page_header' => [
            'eyebrow' => __('media.library.index.eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? $scope['title'] ?? __('media.library.form.create_title')),
            'description' => $media !== null ? __('media.library.form.hero_lede_edit') : __('media.library.form.hero_lede_create'),
            'actions' => [
                ['label' => __('media.library.form.hero_manage_fields'), 'href' => '/workspaces/media-fields', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-sliders'],
                ['label' => __('media.library.form.actions.back'), 'href' => '/workspaces/media-library', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],
    ];
};
