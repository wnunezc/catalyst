<?php

declare(strict_types=1);

return static function (array $scope): array {
    return [
        'google_enabled' => (bool) ($scope['google_enabled'] ?? $scope['googleEnabled'] ?? false),
        'github_enabled' => (bool) ($scope['github_enabled'] ?? $scope['githubEnabled'] ?? false),
        'or_label' => (string) ($scope['or_label'] ?? $scope['orLabel'] ?? __('auth.login.or')),
    ];
};
