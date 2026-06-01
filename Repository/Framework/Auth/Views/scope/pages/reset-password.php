<?php

declare(strict_types=1);

use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $passwordError = validation_error('password');
    $passwordConfirmError = validation_error('password_confirm');

    $passwordPolicy = $scope['passwordPolicy'] ?? $scope['password_policy'] ?? [
        'minLength' => 8,
        'requireUppercase' => false,
        'requireNumber' => false,
        'requireSymbol' => false,
    ];

    return [
        'title' => (string) ($scope['title'] ?? __('auth.reset_password.title')),
        'token' => (string) ($scope['token'] ?? ''),
        'csrf_token_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'password_policy' => is_array($passwordPolicy) ? $passwordPolicy : [],
        'password_policy_json' => InlineJson::encode($passwordPolicy),
        'password_error' => (string) ($passwordError ?? ''),
        'password_invalid_class' => $passwordError !== null ? ' is-invalid' : '',
        'password_confirm_error' => (string) ($passwordConfirmError ?? ''),
        'password_confirm_invalid_class' => $passwordConfirmError !== null ? ' is-invalid' : '',
    ];
};
