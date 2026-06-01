<?php

declare(strict_types=1);

use Catalyst\Framework\Appearance\PlatformAppearanceManager;

return static function (array $scope): array {
    $branding = PlatformAppearanceManager::getInstance()->brandingViewModel();
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
        'document_title' => $title !== $brandName ? $title . ' - ' . $brandName : $title,
        'lang' => (string) ($scope['lang'] ?? 'en'),
        'meta_tags' => $metaTags,
        'style_links' => $styleLinks,
        'script_links' => $scriptLinks,
    ];
};
