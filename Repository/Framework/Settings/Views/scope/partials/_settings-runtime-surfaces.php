<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'operationsUrl' => (string) ($scope['operationsUrl'] ?? '/operations'),
    ];
};
