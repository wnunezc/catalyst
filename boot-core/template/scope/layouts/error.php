<?php

declare(strict_types=1);

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
