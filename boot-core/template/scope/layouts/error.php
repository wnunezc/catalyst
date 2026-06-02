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
    $brandName = (string) ($branding['brand_name'] ?? 'Catalyst Framework');
    $title = (string) ($scope['title'] ?? $brandName);
    $metaTags = [];

    foreach ((array) ($scope['meta'] ?? []) as $name => $content) {
        $metaTags[] = [
            'name' => (string) $name,
            'content' => (string) $content,
        ];
    }

    $asset = implode(DS, [PD, 'public', 'assets', 'css', 'catalyst', 'error-surface.css']);

    return [
        'branding' => $branding,
        'brand_name' => $brandName,
        'document_title' => $title !== $brandName ? $title . ' - ' . $brandName : $title,
        'lang' => (string) ($scope['lang'] ?? 'en'),
        'meta_tags' => $metaTags,
        'error_surface_asset_version' => (int) (@filemtime($asset) ?: time()),
    ];
};
