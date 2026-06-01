<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $user = is_array($scope['authUser'] ?? null) ? $scope['authUser'] : [];

    return [
        'auth_check' => (bool) ($scope['authCheck'] ?? false),
        'auth_user' => [
            'id' => (string) ($user['id'] ?? ''),
            'name' => (string) ($user['name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'role' => (string) ($user['role'] ?? ''),
        ],
        'logout_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
