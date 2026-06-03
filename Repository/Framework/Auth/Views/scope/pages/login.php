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
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
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
        'registration_enabled' => FeatureFlagManager::getInstance()->isRuntimeEnabled('auth.registration_enabled'),
        'has_social_login' => $googleEnabled || $githubEnabled,
        'google_enabled' => $googleEnabled,
        'github_enabled' => $githubEnabled,
        'or_label' => __('auth.login.or'),
    ];
};