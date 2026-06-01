<?php

declare(strict_types=1);

return static function (array $scope): array {
    $version = (string) ($scope['version'] ?? '');
    $phpVersion = (string) ($scope['phpVersion'] ?? '');

    return [
        'show_login' => (bool) ($scope['isConfigured'] ?? false),
        'version_line' => sprintf(__('devtools.route_test.version_line'), $version, $phpVersion),
    ];
};
