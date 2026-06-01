<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $emailError = validation_error('email');

    return [
        'title' => (string) ($scope['title'] ?? __('auth.forgot_password.title')),
        'csrf_token_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'email_value' => (string) old('email', ''),
        'email_error' => (string) ($emailError ?? ''),
        'email_invalid_class' => $emailError !== null ? ' is-invalid' : '',
    ];
};
