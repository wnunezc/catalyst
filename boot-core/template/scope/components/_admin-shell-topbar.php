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

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $authUser = AuthManager::getInstance()->user() ?? [];
    $authName = trim((string) ($scope['auth_name'] ?? $scope['authName'] ?? ($authUser['name'] ?? '')));
    $authEmail = trim((string) ($scope['auth_email'] ?? $scope['authEmail'] ?? ($authUser['email'] ?? '')));

    return [
        'auth_name' => $authName !== '' ? $authName : __('ui.product_nav.fallback_user'),
        'auth_email' => $authEmail,
        'has_auth_email' => $authEmail !== '',
        'auth_menu_label' => (string) ($scope['auth_menu_label'] ?? __('ui.product_nav.account_toggle')),
        'auth_role' => (string) ($scope['auth_role'] ?? $scope['authRole'] ?? ($authUser['role'] ?? '')),
        'auth_avatar_src' => (string) ($scope['auth_avatar_src'] ?? '/assets/vendor/inspinia/images/users/user-1.jpg'),
        'logout_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
