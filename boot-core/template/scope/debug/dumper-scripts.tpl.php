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

    return [
        'nonce_attr' => TrustedHtml::fromString($nonceAttr),
        'collapsible_js' => TrustedHtml::fromString((string) ($scope['collapsibleJs'] ?? '')),
        'modal_id_js' => TrustedHtml::fromString(json_encode((string) ($scope['modalId'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '""'),
    ];
};
