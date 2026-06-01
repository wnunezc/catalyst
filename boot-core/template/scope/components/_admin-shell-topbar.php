<?php

declare(strict_types=1);

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
        'auth_avatar_src' => (string) ($scope['auth_avatar_src'] ?? '/assets/img/inspinia/users/user-1.jpg'),
        'logout_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
