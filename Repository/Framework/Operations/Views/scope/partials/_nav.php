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
    $items = [
        'overview' => ['label' => __('operations.nav.overview'), 'href' => '/operations'],
        'module-designer' => ['label' => __('operations.module_designer.page_title'), 'href' => '/workspaces/module-designer'],
        'localization' => ['label' => __('operations.localization.page_title'), 'href' => '/workspaces/locale-tools'],
        'appearance' => ['label' => __('operations.appearance.page_title'), 'href' => '/configuration/platform-appearance'],
        'feature-flags' => ['label' => __('operations.feature_flags.title'), 'href' => '/configuration/feature-flags'],
        'plugins' => ['label' => __('operations.plugins.title'), 'href' => '/configuration/plugins'],
        'deployments' => ['label' => __('operations.deployments.title'), 'href' => '/operations/deployments'],
        'tenancy' => ['label' => __('operations.tenancy.title'), 'href' => '/operations/tenancy'],
    ];

    $activeSection = (string) ($scope['activeSection'] ?? '');
    $navItems = [];

    foreach ($items as $key => $item) {
        $navItems[] = [
            'href' => (string) $item['href'],
            'label' => (string) $item['label'],
            'is_active' => $activeSection === $key,
        ];
    }

    return [
        'aria_label' => __('operations.nav.aria_label'),
        'nav_items' => $navItems,
    ];
};
