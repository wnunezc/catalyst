<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Helpers\Security\CsrfProtection;

/**
 * Builds the shared topbar scope for canonical document templates.
 *
 * Responsibility: Restores the topbar account/workspace contract without embedding PHP in templates.
 */
final class TopbarViewModel
{
    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public static function build(array $scope): array
    {
        $authUser = AuthManager::getInstance()->user() ?? [];
        $authName = self::nonEmptyString(
            (string) ($authUser['name'] ?? $scope['auth_name'] ?? $scope['authName'] ?? ''),
            __('ui.product_nav.fallback_user')
        );
        $authEmail = trim((string) ($authUser['email'] ?? $scope['auth_email'] ?? $scope['authEmail'] ?? ''));
        $authRole = trim((string) ($authUser['role'] ?? $scope['auth_role'] ?? $scope['authRole'] ?? ''));
        $isPublicSurface = !empty($scope['is_public_surface']);
        $isAccountSurface = !empty($scope['is_account_surface']) && !empty($scope['is_authenticated']);
        $isInternalSurface = !$isPublicSurface && empty($scope['is_account_guest']);
        $topbarClass = self::topbarClass((string) ($scope['topbar_class'] ?? 'app-topbar'));
        $menuItems = self::accountMenuItems($scope, $isAccountSurface, $isInternalSurface);

        return [
            'topbar_class' => $topbarClass,
            'topbar_is_account_surface' => $isAccountSurface,
            'topbar_is_internal_surface' => $isInternalSurface,
            'topbar_show_search' => self::boolScope($scope, 'topbar_show_search', $isInternalSurface && !$isAccountSurface),
            'topbar_search_placeholder' => (string) ($scope['topbar_search_placeholder'] ?? 'Search for something...'),
            'topbar_show_workspace_button' => self::boolScope($scope, 'topbar_show_workspace_button', $isInternalSurface && !$isAccountSurface),
            'topbar_workspace_button_label' => (string) ($scope['topbar_workspace_button_label'] ?? __('ui.shell.workspace')),
            'topbar_workspace_button_title' => (string) ($scope['topbar_workspace_button_title'] ?? __('ui.shell.workspace')),
            'topbar_workspace_icon_class' => self::safeIconClass((string) ($scope['topbar_workspace_icon_class'] ?? 'ti ti-building-community topbar-link-icon'), 'ti ti-building-community topbar-link-icon'),
            'topbar_workspace_label' => (string) ($scope['topbar_workspace_label'] ?? __('account.shell.workspace')),
            'topbar_page_title' => self::nonEmptyString((string) ($scope['topbar_page_title'] ?? $scope['pageTitle'] ?? $scope['title'] ?? ''), ''),
            'topbar_has_page_title' => trim((string) ($scope['topbar_page_title'] ?? $scope['pageTitle'] ?? $scope['title'] ?? '')) !== '',
            'auth_name' => $authName,
            'auth_email' => $authEmail,
            'has_auth_email' => $authEmail !== '',
            'auth_role' => $authRole,
            'has_auth_role' => $authRole !== '',
            'auth_avatar_src' => (string) ($scope['auth_avatar_src'] ?? '/assets/vendor/inspinia/images/users/user-1.jpg'),
            'auth_menu_label' => self::nonEmptyString((string) ($scope['auth_menu_label'] ?? __('ui.product_nav.account_toggle')), __('ui.product_nav.account_toggle')),
            'topbar_account_menu_label' => self::nonEmptyString((string) ($scope['topbar_account_menu_label'] ?? $scope['auth_menu_label'] ?? __('ui.product_nav.account_toggle')), __('ui.product_nav.account_toggle')),
            'topbar_account_menu_title' => self::nonEmptyString((string) ($scope['topbar_account_menu_title'] ?? $scope['auth_menu_label'] ?? __('ui.product_nav.account_toggle')), __('ui.product_nav.account_toggle')),
            'topbar_account_menu_items' => $menuItems,
            'topbar_has_account_menu_items' => $menuItems !== [],
            'logout_csrf_field' => $scope['logout_csrf_field'] ?? TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function accountMenuItems(array $scope, bool $isAccountSurface, bool $isInternalSurface): array
    {
        if (is_array($scope['topbar_account_menu_items'] ?? null)) {
            return $scope['topbar_account_menu_items'];
        }

        if ($isAccountSurface) {
            return [
                self::menuItem(__('account.nav.profile'), '/account/profile', 'ti ti-user-circle'),
                self::menuItem(__('account.nav.mfa'), '/account/security/mfa', 'ti ti-2fa'),
                self::menuItem(__('account.nav.recovery'), '/account/recovery', 'ti ti-lifebuoy'),
            ];
        }

        if (!$isInternalSurface) {
            return [];
        }

        return [
            self::menuItem(__('ui.product_nav.profile'), '/account/profile', 'ti ti-user-circle'),
            self::menuItem(__('ui.product_nav.account_settings'), '/account/security', 'ti ti-settings-2'),
            self::menuItem(__('ui.product_nav.support'), '/account/recovery/support', 'ti ti-headset'),
        ];
    }

    /** @return array<string, mixed> */
    private static function menuItem(string $label, string $href, string $iconClass): array
    {
        return [
            'label' => $label,
            'href' => $href,
            'icon_class' => self::safeIconClass($iconClass, 'ti ti-circle'),
        ];
    }

    /** @param array<string, mixed> $scope */
    private static function boolScope(array $scope, string $key, bool $default): bool
    {
        return array_key_exists($key, $scope) ? (bool) $scope[$key] : $default;
    }

    private static function topbarClass(string $class): string
    {
        return trim($class) !== '' ? trim($class) : 'app-topbar';
    }

    private static function safeIconClass(string $class, string $default): string
    {
        $class = trim($class);
        if ($class === '') {
            return $default;
        }

        return preg_match('/^[a-zA-Z0-9\s:_-]+$/', $class) === 1 ? $class : $default;
    }

    private static function nonEmptyString(string $value, string $fallback): string
    {
        $value = trim($value);
        return $value !== '' ? $value : $fallback;
    }
}
