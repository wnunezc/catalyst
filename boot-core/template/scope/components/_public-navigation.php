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

use Catalyst\Framework\Appearance\PlatformAppearanceManager;

return static function (array $scope): array {
    $branding = PlatformAppearanceManager::getInstance()->brandingViewModel();
    $items = [];

    foreach ((array) ($scope['publicNavigation'] ?? []) as $item) {
        $isActive = (bool) ($item['active'] ?? false);
        $items[] = [
            'href' => (string) ($item['href'] ?? '/'),
            'label' => (string) ($item['label'] ?? __('ui.datagrid.action')),
            'hint' => (string) ($item['hint'] ?? ''),
            'is_active' => $isActive,
            'link_class' => 'catalyst-public-nav__link' . ($isActive ? ' is-active' : ''),
        ];
    }

    return [
        'branding' => $branding,
        'brand_name' => (string) ($branding['brand_name'] ?? __('ui.shell.public_routes')),
        'brand_tagline' => __('ui.shell.public_tagline'),
        'brand_alt' => (string) ($branding['brand_name'] ?? 'Brand'),
        'brand_logo_light_url' => (string) ($branding['logo_light_url'] ?? ''),
        'brand_logo_dark_url' => (string) ($branding['logo_dark_url'] ?? $branding['logo_light_url'] ?? ''),
        'has_brand_logo' => (string) ($branding['logo_light_url'] ?? '') !== '',
        'has_brand_tagline' => true,
        'public_navigation_items' => $items,
    ];
};
