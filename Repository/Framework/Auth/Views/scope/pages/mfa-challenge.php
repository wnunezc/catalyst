<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $codeError = validation_error('code');

    return [
        'title' => (string) ($scope['title'] ?? __('auth.mfa.challenge_title')),
        'csrf_token_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'code_error' => (string) ($codeError ?? ''),
        'code_invalid_class' => $codeError !== null ? ' is-invalid' : '',
    ];
};
