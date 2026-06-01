<?php

declare(strict_types=1);

use Catalyst\Framework\Appearance\PlatformAppearanceManager;

return static function (array $scope): array {
    $authCssPath = implode(DS, [PD, 'public', 'assets', 'css', 'catalyst', 'auth.css']);
    $appearance = PlatformAppearanceManager::getInstance();
    $branding = $appearance->brandingViewModel();
    $runtime = $appearance->runtimeViewModel();
    $runtimeDefaults = is_array($runtime['defaults'] ?? null) ? $runtime['defaults'] : [];
    $lockedConfig = is_array($runtime['lockedConfig'] ?? null) ? $runtime['lockedConfig'] : [];
    $initialConfig = (bool) ($runtime['adminCustomizerEnabled'] ?? true)
        ? $runtimeDefaults
        : array_replace($runtimeDefaults, $lockedConfig);

    $brandName = (string) ($branding['brand_name'] ?? 'Catalyst');
    $title = (string) ($scope['title'] ?? $brandName);
    $metaTags = [];
    $styleLinks = [];
    $scriptLinks = [];

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
        'brand_logo_light_url' => (string) ($branding['logo_light_url'] ?? ''),
        'brand_logo_dark_url' => (string) ($branding['logo_dark_url'] ?? $branding['logo_light_url'] ?? ''),
        'favicon_url' => (string) ($branding['favicon_url'] ?? ''),
        'has_brand_logo' => (string) ($branding['logo_light_url'] ?? '') !== '',
        'has_brand_tagline' => trim((string) ($branding['brand_tagline'] ?? '')) !== '',
        'auth_asset_version' => (int) (@filemtime($authCssPath) ?: time()),
        'document_title' => $title !== $brandName ? $title . ' - ' . $brandName : $title,
        'lang' => (string) ($scope['lang'] ?? 'en'),
        'html_dir' => (string) ($initialConfig['dir'] ?? 'ltr'),
        'html_theme' => (string) (($initialConfig['theme'] ?? 'light') === 'system' ? 'light' : ($initialConfig['theme'] ?? 'light')),
        'html_skin' => (string) ($initialConfig['skin'] ?? 'default'),
        'html_layout_width' => (string) ($initialConfig['width'] ?? 'fluid'),
        'html_layout_position' => (string) ($initialConfig['position'] ?? 'fixed'),
        'html_sidenav_color' => (string) ($initialConfig['sidenav-color'] ?? 'dark'),
        'html_sidenav_size' => (string) ($initialConfig['sidenav-size'] ?? 'default'),
        'html_topbar_color' => (string) ($initialConfig['topbar-color'] ?? 'gray'),
        'auth_surface_title' => 'Catalyst Framework',
        'auth_surface_subtitle' => 'Secure administration gateway',
        'auth_surface_note' => 'Session protected · RBAC ready · MFA capable',
        'auth_fact_session_label' => 'Session',
        'auth_fact_session_value' => 'Protected',
        'auth_fact_access_label' => 'Access',
        'auth_fact_access_value' => 'RBAC ready',
        'auth_fact_runtime_label' => 'Runtime',
        'auth_fact_runtime_value' => 'Auditable',
        'meta_tags' => $metaTags,
        'style_links' => $styleLinks,
        'script_links' => $scriptLinks,
    ];
};
