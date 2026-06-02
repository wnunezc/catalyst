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
    return [
        'admin_header' => [
            'eyebrow' => __('media.library.index.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('media.library.index.title')),
            'description' => __('media.library.index.hero_lede'),
            'actions' => [
                ['label' => __('media.library.index.hero_manage_fields'), 'href' => '/workspaces/media-fields', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-sliders'],
                ['label' => __('media.library.index.hero_upload_asset'), 'href' => '/workspaces/media-library/upload', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-upload'],
            ],
        ],
    ];
};
