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

namespace Catalyst\Repository\Account\Support;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Helpers\I18n\Translator;

/**
 * Builds account-specific scope data for the canonical document and shell.
 *
 * @package Catalyst\Repository\Account\Support
 * Responsibility: Provides account navigation and presentation metadata without defining a parallel shell.
 */
final class AccountSurfaceViewModel
{
    /**
     * Builds shell data for a signed-in account page.
     *
     * Responsibility: Builds shell data for a signed-in account page.
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public function authenticated(array $scope = []): array
    {
        return $this->build($scope, true);
    }

    /**
     * Builds shell data for a public account recovery page.
     *
     * Responsibility: Builds shell data for a public account recovery page.
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public function guest(array $scope = []): array
    {
        return $this->build($scope, false);
    }

    /**
     * Merges account shell defaults with page scope for authenticated or guest rendering.
     *
     * Responsibility: Merges account shell defaults with page scope for authenticated or guest rendering.
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    private function build(array $scope, bool $authenticated): array
    {
        $authUser = AuthManager::getInstance()->user() ?? [];
        $authName = trim((string) ($authUser['name'] ?? ''));
        $authEmail = trim((string) ($authUser['email'] ?? ''));
        $title = (string) ($scope['title'] ?? 'Catalyst');
        $layout = $authenticated
            ? [
                'body_class' => 'catalyst-shell-body account-page-body',
                'shell_class' => 'wrapper',
                'topbar_class' => 'app-topbar',
                'sidebar_class' => 'sidenav-menu',
                'content_class' => 'content-page',
            ]
            : [
                'body_class' => 'catalyst-shell-body account-page-body account-guest-body',
                'shell_class' => 'account-guest-shell',
                'topbar_class' => 'app-topbar',
                'sidebar_class' => 'sidenav-menu',
                'content_class' => 'account-guest-content',
            ];

        return array_merge([
            'document_title' => $title,
            'lang' => (string) ($scope['lang'] ?? Translator::getInstance()->getLocale()),
            'is_authenticated' => $authenticated,
            'auth_name' => $authName !== '' ? $authName : __('account.shell.fallback_user'),
            'auth_email' => $authEmail,
            'has_auth_email' => $authEmail !== '',
            'auth_avatar_src' => (string) ($scope['auth_avatar_src'] ?? '/assets/vendor/inspinia/images/users/user-1.jpg'),
            'auth_menu_label' => __('ui.product_nav.account_toggle'),
            'navigation_model' => 'application',
            'has_breadcrumbs' => !empty($scope['breadcrumb_items']),
            'breadcrumb_items' => (array) ($scope['breadcrumb_items'] ?? []),
            'status_bar_show_theme_toggle' => true,
            'status_bar_theme_toggle_attribute' => 'data-catalyst-theme-toggle',
            'status_bar_theme_toggle_icon_class' => 'ti ti-moon',
            'status_bar_show_customizer_toggle' => false,
            'status_bar_context' => 'account',
            'suppress_work_assets' => true,
            'is_account_surface' => true,
            'is_account_guest' => !$authenticated,
            'show_topbar' => $authenticated,
            'show_sidebar' => $authenticated,
            'show_status_bar' => true,
            'show_theme_customizer' => false,
            'show_auth_brand_panel' => false,
            'surface_context' => 'account',
        ], $layout, $scope);
    }

}
