<?php

declare(strict_types=1);

use Catalyst\Framework\Session\FlashMessage;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;

return static function (): array {
    $flash = FlashMessage::getInstance();
    $regular = $flash->all();
    $persistent = $flash->allPersistent();

    if (empty($regular) && empty($persistent)) {
        return [
            'has_flash_payload' => false,
            'flash_payload_json' => null,
        ];
    }

    return [
        'has_flash_payload' => true,
        'flash_payload_json' => TrustedHtml::fromString(InlineJson::encode([
            'regular' => $regular,
            'persistent' => $persistent,
        ])),
    ];
};
