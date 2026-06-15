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

use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $passwordError = validation_error('password');
    $passwordConfirmError = validation_error('password_confirm');

    $passwordPolicy = $scope['passwordPolicy'] ?? $scope['password_policy'] ?? [
        'minLength' => 12,
        'requireUppercase' => true,
        'requireLowercase' => true,
        'requireNumber' => true,
        'requireSymbol' => true,
        'blockCommon' => true,
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
