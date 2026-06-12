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
        'page_header' => [
            'eyebrow' => __('roles.roles.hero_eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.roles.title')),
            'description' => __('roles.roles.hero_lede'),
            'actions' => [
                ['label' => __('roles.roles.permissions_link'), 'href' => '/users/permissions', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-key'],
                ['label' => __('roles.roles.new'), 'href' => '/users/roles/create', 'class' => 'btn btn-sm btn-primary', 'icon' => 'fa-solid fa-plus'],
            ],
        ],
    ];
};
