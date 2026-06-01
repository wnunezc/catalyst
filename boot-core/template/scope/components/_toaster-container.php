<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'toaster_position' => (string) ($scope['toasterPosition'] ?? 'top-right'),
    ];
};
