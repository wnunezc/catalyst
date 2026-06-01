<?php

declare(strict_types=1);

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;
use Catalyst\Helpers\Security\CspNonce;

return static function (array $scope = []): array {
    $assetVersion = static function (array $segments): int {
        $path = implode(DS, array_merge([PD, 'public'], $segments));

        return (int) (@filemtime($path) ?: time());
    };

    $faviconUrl = trim((string) ($scope['favicon_url'] ?? ''));
    $faviconType = '';

    if ($faviconUrl !== '') {
        $faviconPath = (string) (parse_url($faviconUrl, PHP_URL_PATH) ?? '');
        $extension = strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION));
        $faviconType = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            default => '',
        };
    }

    return [
        'csp_nonce' => CspNonce::get(),
        'csrf_meta_tag' => TrustedHtml::fromString(CsrfProtection::getInstance()->getMetaTag()),
        'has_favicon' => $faviconUrl !== '',
        'favicon_url' => $faviconUrl,
        'favicon_type' => $faviconType,
        'has_favicon_type' => $faviconType !== '',
        'platform_appearance_json' => TrustedHtml::fromString(InlineJson::encode(PlatformAppearanceManager::getInstance()->runtimeViewModel())),
        'status_bar_style_version' => $assetVersion(['assets', 'css', 'catalyst', 'status-bar.css']),
    ];
};
