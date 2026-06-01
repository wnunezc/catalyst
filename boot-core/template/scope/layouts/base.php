<?php

declare(strict_types=1);

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CspNonce;

return static function (array $scope): array {
    $branding = PlatformAppearanceManager::getInstance()->brandingViewModel();
    $appearanceRuntime = PlatformAppearanceManager::getInstance()->runtimeViewModel();
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

    $appendStyle = static function (string $href, string $rel = 'stylesheet', string $media = '') use (&$styleLinks): void {
        if ($href === '') {
            return;
        }

        foreach ($styleLinks as $existing) {
            if (($existing['href'] ?? '') === $href) {
                return;
            }
        }

        $styleLinks[] = [
            'href' => $href,
            'rel' => $rel,
            'has_media' => $media !== '',
            'media' => $media,
        ];
    };

    foreach ((array) ($scope['styles'] ?? []) as $style) {
        if (is_array($style)) {
            $media = trim((string) ($style['media'] ?? ''));
            $appendStyle((string) ($style['href'] ?? ''), (string) ($style['rel'] ?? 'stylesheet'), $media);
            continue;
        }

        $appendStyle((string) $style);
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
        'brand_tagline' => __('ui.shell.public_tagline'),
        'document_title' => $title !== $brandName ? $title . ' - ' . $brandName : $title,
        'lang' => (string) ($scope['lang'] ?? 'en'),
        'meta_tags' => $metaTags,
        'style_links' => $styleLinks,
        'script_links' => $scriptLinks,
        'publicNavigation' => is_array($scope['publicNavigation'] ?? null) ? $scope['publicNavigation'] : [],
        'csp_nonce' => (string) ($scope['csp_nonce'] ?? CspNonce::get()),
        'platform_appearance_json' => TrustedHtml::fromString(InlineJson::encode($appearanceRuntime)),
        'status_bar_style_version' => $assetVersion(['assets', 'css', 'catalyst', 'status-bar.css']),
        'demo_ui_page_slug' => (string) ($scope['demo_ui_page_slug'] ?? trim((string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/'), '/')),
        'status_bar_show_theme_toggle' => array_key_exists('status_bar_show_theme_toggle', $scope) ? (bool) $scope['status_bar_show_theme_toggle'] : true,
        'status_bar_theme_toggle_attribute' => (string) ($scope['status_bar_theme_toggle_attribute'] ?? 'data-demoui-theme-toggle'),
        'status_bar_theme_toggle_icon_class' => (string) ($scope['status_bar_theme_toggle_icon_class'] ?? 'ti ti-circle-half-2'),
        'status_bar_show_customizer_toggle' => array_key_exists('status_bar_show_customizer_toggle', $scope) ? (bool) $scope['status_bar_show_customizer_toggle'] : true,
        'status_bar_customizer_toggle_attribute' => (string) ($scope['status_bar_customizer_toggle_attribute'] ?? 'data-theme-customizer-toggle'),
        'status_bar_customizer_toggle_icon_class' => (string) ($scope['status_bar_customizer_toggle_icon_class'] ?? 'ti ti-palette'),
        'status_bar_customizer_toggle_aria_label' => (string) ($scope['status_bar_customizer_toggle_aria_label'] ?? __('ui.status_bar.open_theme_customizer')),
        'status_bar_customizer_toggle_title' => (string) ($scope['status_bar_customizer_toggle_title'] ?? __('ui.status_bar.theme_customizer')),
        'suppress_work_assets' => true,
        'is_public_shell' => true,
    ];
};
