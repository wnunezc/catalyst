<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;

return static function (array $scope): array {
    $nonceAttr = '';
    if (class_exists(\Catalyst\Helpers\Security\CspNonce::class)) {
        $nonce = \Catalyst\Helpers\Security\CspNonce::get();
        if ($nonce !== '') {
            $nonceAttr = ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"';
        }
    }

    $themeColors = is_array($scope['themeColors'] ?? null) ? $scope['themeColors'] : [];

    return [
        'nonce_attr' => TrustedHtml::fromString($nonceAttr),
        'theme_meta_color' => (string) ($themeColors['meta'] ?? '#999999'),
        'theme_label_color' => (string) ($themeColors['label'] ?? '#006699'),
    ];
};
