<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $tokenError = validation_error('token');

    return [
        'title' => (string) ($scope['title'] ?? __('auth.verify.title')),
        'csrf_token_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'token_value' => (string) old('token', ''),
        'token_error' => (string) ($tokenError ?? ''),
        'token_invalid_class' => $tokenError !== null ? ' is-invalid' : '',
    ];
};
