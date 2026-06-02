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

namespace App\Surface\Account\Support;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Helpers\Security\CspNonce;
use Catalyst\Helpers\Security\CsrfProtection;

/**
 * Defines the Account Shell View Model class contract.
 *
 * @package App\Surface\Account\Support
 * Responsibility: Coordinates the account shell view model behavior within its module boundary.
 */
final class AccountShellViewModel
{
    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public function authenticated(array $scope = []): array
    {
        return $this->build($scope, true);
    }

    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public function guest(array $scope = []): array
    {
        return $this->build($scope, false);
    }

    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    private function build(array $scope, bool $authenticated): array
    {
        $currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
        $authUser = AuthManager::getInstance()->user() ?? [];
        $authName = trim((string) ($authUser['name'] ?? ''));
        $authEmail = trim((string) ($authUser['email'] ?? ''));
        $brandName = (string) (PlatformAppearanceManager::getInstance()->brandingViewModel()['brand_name'] ?? 'Catalyst');
        $title = (string) ($scope['title'] ?? $brandName);

        return array_merge([
            'document_title' => $title !== $brandName ? $title . ' - ' . $brandName : $title,
            'lang' => (string) ($scope['lang'] ?? Translator::getInstance()->getLocale()),
            'is_authenticated' => $authenticated,
            'auth_name' => $authName !== '' ? $authName : __('account.shell.fallback_user'),
            'auth_email' => $authEmail,
            'has_auth_email' => $authEmail !== '',
            'auth_avatar_src' => (string) ($scope['auth_avatar_src'] ?? '/assets/vendor/inspinia/images/users/user-1.jpg'),
            'auth_menu_label' => __('ui.product_nav.account_toggle'),
            'logout_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
            'csp_nonce' => CspNonce::get(),
            'platform_appearance_json' => TrustedHtml::fromString(InlineJson::encode(PlatformAppearanceManager::getInstance()->runtimeViewModel())),
            'csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
            'csrf_meta_tag' => TrustedHtml::fromString(CsrfProtection::getInstance()->getMetaTag()),
            'account_nav_groups' => $this->navGroups($currentPath),
            'has_breadcrumbs' => !empty($scope['breadcrumb_items']),
            'breadcrumb_items' => (array) ($scope['breadcrumb_items'] ?? []),
            'status_bar_show_theme_toggle' => true,
            'status_bar_theme_toggle_attribute' => 'data-catalyst-theme-toggle',
            'status_bar_theme_toggle_icon_class' => 'ti ti-moon',
            'status_bar_show_customizer_toggle' => false,
            'status_bar_context' => 'account',
            'suppress_work_assets' => true,
            'is_public_shell' => true,
        ], $scope);
    }

    /** @return list<array<string, mixed>> */
    private function navGroups(string $currentPath): array
    {
        $makeItem = static function (string $label, string $href, string $icon, string $hint) use ($currentPath): array {
            $active = $currentPath === $href || ($href !== '/dashboard' && str_starts_with($currentPath, $href . '/'));

            return [
                'is_title' => false,
                'has_children' => false,
                'label' => $label,
                'href' => $href,
                'icon' => $icon,
                'hint' => $hint,
                'is_active' => $active,
            ];
        };

        $makeChild = static function (string $label, string $href, string $hint) use ($currentPath): array {
            $active = $currentPath === $href || str_starts_with($currentPath, $href . '/');

            return [
                'label' => $label,
                'href' => $href,
                'hint' => $hint,
                'is_active' => $active,
            ];
        };

        $makeGroup = static function (string $label, string $collapseId, string $icon, string $hint, array $items): array {
            $active = false;
            foreach ($items as $item) {
                if (!empty($item['is_active'])) {
                    $active = true;
                    break;
                }
            }

            return [
                'is_title' => false,
                'has_children' => true,
                'label' => $label,
                'collapse_id' => $collapseId,
                'icon' => $icon,
                'hint' => $hint,
                'items' => $items,
                'is_active' => $active,
                'show' => $active,
                'expanded' => $active ? 'true' : 'false',
            ];
        };

        $mfaItems = [
            $makeChild(__('account.nav.mfa_manage'), '/account/security/mfa', __('account.nav_hints.mfa_manage')),
            $makeChild(__('account.nav.mfa_recovery'), '/account/recovery/mfa', __('account.nav_hints.mfa_recovery')),
        ];

        $recoveryItems = [
            $makeChild(__('account.nav.recovery_support'), '/account/recovery/support', __('account.nav_hints.recovery_support')),
            $makeChild(__('account.nav.recovery_compromised'), '/account/recovery/compromised', __('account.nav_hints.recovery_compromised')),
        ];

        return [
            ['is_title' => true, 'label' => __('account.nav.account')],
            $makeItem(__('account.nav.dashboard'), '/dashboard', 'ti ti-layout-dashboard', __('account.nav_hints.dashboard')),
            $makeItem(__('account.nav.profile'), '/account/profile', 'ti ti-user-circle', __('account.nav_hints.profile')),
            $makeGroup(__('account.nav.mfa'), 'account-nav-mfa', 'ti ti-2fa', __('account.nav_hints.mfa'), $mfaItems),
            $makeGroup(__('account.nav.recovery'), 'account-nav-recovery', 'ti ti-lifebuoy', __('account.nav_hints.recovery'), $recoveryItems),
            $makeItem(__('account.nav.activity'), '/account/activity', 'ti ti-history', __('account.nav_hints.activity')),
        ];
    }
}
