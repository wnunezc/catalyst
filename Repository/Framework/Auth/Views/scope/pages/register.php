<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

use Catalyst\Framework\Auth\OAuthManager;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $oauth = OAuthManager::getInstance();
    $googleEnabled = $oauth->isConfigured('google');
    $githubEnabled = $oauth->isConfigured('github');

    $nameError = validation_error('name');
    $emailError = validation_error('email');
    $passwordError = validation_error('password');
    $passwordConfirmError = validation_error('password_confirm');

    $passwordPolicy = $scope['passwordPolicy'] ?? $scope['password_policy'] ?? [
        'minLength' => 8,
        'requireUppercase' => false,
        'requireNumber' => false,
        'requireSymbol' => false,
    ];

    return [
        'title' => (string) ($scope['title'] ?? __('auth.register.title')),
        'csrf_token_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'name_value' => (string) old('name', ''),
        'name_error' => (string) ($nameError ?? ''),
        'name_invalid_class' => $nameError !== null ? ' is-invalid' : '',
        'email_value' => (string) old('email', ''),
        'email_error' => (string) ($emailError ?? ''),
        'email_invalid_class' => $emailError !== null ? ' is-invalid' : '',
        'password_error' => (string) ($passwordError ?? ''),
        'password_invalid_class' => $passwordError !== null ? ' is-invalid' : '',
        'password_confirm_error' => (string) ($passwordConfirmError ?? ''),
        'password_confirm_invalid_class' => $passwordConfirmError !== null ? ' is-invalid' : '',
        'password_policy' => is_array($passwordPolicy) ? $passwordPolicy : [],
        'password_policy_json' => InlineJson::encode($passwordPolicy),
        'has_social_login' => $googleEnabled || $githubEnabled,
        'google_enabled' => $googleEnabled,
        'github_enabled' => $githubEnabled,
        'or_label' => __('auth.register.or'),
    ];
};
