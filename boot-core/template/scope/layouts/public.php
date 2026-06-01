<?php

declare(strict_types=1);

use Catalyst\Framework\Appearance\PlatformAppearanceManager;

return static function (array $scope): array {
    $appearance = PlatformAppearanceManager::getInstance();
    $branding = $appearance->brandingViewModel();
    $brandName = (string) ($branding['brand_name'] ?? 'Catalyst Framework');
    $title = (string) ($scope['title'] ?? $brandName);
    $metaTags = [];
    $styleLinks = [];
    $scriptLinks = [];
    $assetVersion = static function (array $segments): int {
        $path = implode(DS, array_merge([PD, 'public'], $segments));

        return (int) (@filemtime($path) ?: time());
    };

    foreach ((array) ($scope['meta'] ?? []) as $name => $content) {
        $metaTags[] = [
            'name' => (string) $name,
            'content' => (string) $content,
        ];
    }

    foreach ((array) ($scope['styles'] ?? []) as $style) {
        if (is_array($style)) {
            $media = trim((string) ($style['media'] ?? ''));
            $styleLinks[] = [
                'href' => (string) ($style['href'] ?? ''),
                'rel' => (string) ($style['rel'] ?? 'stylesheet'),
                'has_media' => $media !== '',
                'media' => $media,
            ];
            continue;
        }

        $styleLinks[] = [
            'href' => (string) $style,
            'rel' => 'stylesheet',
            'has_media' => false,
            'media' => '',
        ];
    }

    foreach ((array) ($scope['scripts'] ?? []) as $script) {
        if (is_array($script)) {
            $type = trim((string) ($script['type'] ?? ''));
            $nonce = trim((string) ($script['nonce'] ?? ''));
            $scriptLinks[] = [
                'src' => (string) ($script['src'] ?? ''),
                'has_type' => $type !== '',
                'type' => $type,
                'defer' => !empty($script['defer']),
                'async' => !empty($script['async']),
                'has_nonce' => $nonce !== '',
                'nonce' => $nonce,
            ];
            continue;
        }

        $scriptLinks[] = [
            'src' => (string) $script,
            'has_type' => false,
            'type' => '',
            'defer' => false,
            'async' => false,
            'has_nonce' => false,
            'nonce' => '',
        ];
    }

    return [
        'branding' => $branding,
        'brand_name' => $brandName,
        'brand_tagline' => (string) ($branding['brand_tagline'] ?? ''),
        'document_title' => $title !== $brandName ? $title . ' - ' . $brandName : $title,
        'lang' => (string) ($scope['lang'] ?? 'en'),
        'meta_tags' => $metaTags,
        'style_links' => $styleLinks,
        'script_links' => $scriptLinks,
        'public_shell_asset_version' => $assetVersion(['assets', 'css', 'catalyst', 'public-shell.css']),
        'publicNavigation' => is_array($scope['publicNavigation'] ?? null) ? $scope['publicNavigation'] : [],
        'status_bar_show_theme_toggle' => array_key_exists('status_bar_show_theme_toggle', $scope)
            ? (bool) $scope['status_bar_show_theme_toggle']
            : true,
        'status_bar_theme_toggle_attribute' => (string) ($scope['status_bar_theme_toggle_attribute'] ?? 'data-migrationui-theme-toggle'),
        'status_bar_theme_toggle_icon_class' => (string) ($scope['status_bar_theme_toggle_icon_class'] ?? 'ti ti-moon'),
        'status_bar_show_customizer_toggle' => $appearance->isAdminCustomizerEnabled() && (array_key_exists('status_bar_show_customizer_toggle', $scope)
            ? (bool) $scope['status_bar_show_customizer_toggle']
            : true),
        'status_bar_customizer_toggle_attribute' => (string) ($scope['status_bar_customizer_toggle_attribute'] ?? 'data-theme-customizer-toggle'),
        'status_bar_customizer_toggle_icon_class' => (string) ($scope['status_bar_customizer_toggle_icon_class'] ?? 'ti ti-settings'),
        'status_bar_customizer_toggle_aria_label' => (string) ($scope['status_bar_customizer_toggle_aria_label'] ?? 'Open theme customizer'),
        'status_bar_customizer_toggle_title' => (string) ($scope['status_bar_customizer_toggle_title'] ?? 'Theme Customizer'),
        'status_bar_context' => (string) ($scope['status_bar_context'] ?? 'public'),
        'is_public_shell' => true,
        'suppress_work_assets' => (bool) ($scope['suppress_work_assets'] ?? false),
    ];
};
