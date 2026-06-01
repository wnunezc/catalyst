<?php

declare(strict_types=1);

use Catalyst\Framework\Auth\OAuthManager;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $oauth = OAuthManager::getInstance();
    $googleEnabled = $oauth->isConfigured('google');
    $githubEnabled = $oauth->isConfigured('github');

    $emailError = validation_error('email');
    $passwordError = validation_error('password');

    return [
        'title' => (string) ($scope['title'] ?? __('auth.login.title')),
        'csrf_token_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'redirect_value' => (string) old('redirect', $scope['redirect'] ?? '/'),
        'email_value' => (string) old('email', $scope['email'] ?? ''),
        'email_error' => (string) ($emailError ?? ''),
        'email_invalid_class' => $emailError !== null ? ' is-invalid' : '',
        'password_error' => (string) ($passwordError ?? ''),
        'password_invalid_class' => $passwordError !== null ? ' is-invalid' : '',
        'remember_checked_attr' => old('remember', '0') === '1' ? ' checked' : '',
        'has_social_login' => $googleEnabled || $githubEnabled,
        'google_enabled' => $googleEnabled,
        'github_enabled' => $githubEnabled,
        'or_label' => __('auth.login.or'),
    ];
};
