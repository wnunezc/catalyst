<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'title' => (string) ($scope['title'] ?? __('devtools.harness.title')),
        'operations_url' => (string) ($scope['operations_url'] ?? $scope['operationsUrl'] ?? '/operations'),
    ];
};
