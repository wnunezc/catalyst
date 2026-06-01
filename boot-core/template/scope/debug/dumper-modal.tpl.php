<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;

return static function (array $scope): array {
    $entries = [];
    foreach ((array) ($scope['data'] ?? []) as $value) {
        $entries[] = [
            'content' => TrustedHtml::fromString((string) $value),
        ];
    }

    return [
        'data_entries' => $entries,
    ];
};
