<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;

return static function (array $scope): array {
    $title = (string) ($scope['title'] ?? __('ui.route_test.title'));
    $version = (string) ($scope['version'] ?? '1.0.0-dev');
    $phpVersion = (string) ($scope['phpVersion'] ?? PHP_VERSION);
    $nonceAttr = '';

    if (class_exists(\Catalyst\Helpers\Security\CspNonce::class)) {
        $nonce = \Catalyst\Helpers\Security\CspNonce::get();
        if ($nonce !== '') {
            $nonceAttr = ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"';
        }
    }

    return [
        'title' => $title,
        'version' => $version,
        'phpVersion' => $phpVersion,
        'style_nonce_attr' => TrustedHtml::fromString($nonceAttr),
    ];
};
