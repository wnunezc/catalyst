<?php

declare(strict_types=1);

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Notification\NotificationRepository;
use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Framework\WebSocket\WebSocketToken;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Security\CspNonce;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope = []): array {
    $auth = AuthManager::getInstance();
    $isAuth = $auth->check();
    $authUser = $isAuth ? ($auth->user() ?? []) : [];
    $wsToken = '';
    $unreadCount = 0;

    if ($isAuth) {
        $wsToken = WebSocketToken::generate((int) $auth->id(), 3600);

        try {
            $unreadCount = NotificationRepository::getInstance()->countUnread((int) $auth->id());
        } catch (\Throwable) {
            $unreadCount = 0;
        }
    }

    $env = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];
    $appUrl = rtrim((string) ($env['APP_URL'] ?? ''), '/');
    $currentUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $wsCfg = ConfigManager::getInstance()->entry('websocket', 'websocket');
    $appearance = PlatformAppearanceManager::getInstance();
    $appearanceRuntime = $appearance->runtimeViewModel();
    $adminCustomizerEnabled = (bool) ($appearanceRuntime['adminCustomizerEnabled'] ?? $appearanceRuntime['admin_customizer_enabled'] ?? true);
    $lockedConfig = is_array($appearanceRuntime['lockedConfig'] ?? null)
        ? $appearanceRuntime['lockedConfig']
        : (is_array($appearanceRuntime['locked_config'] ?? null) ? $appearanceRuntime['locked_config'] : []);
    $closedSkins = is_array($appearanceRuntime['closedSkins'] ?? null)
        ? $appearanceRuntime['closedSkins']
        : (is_array($appearanceRuntime['closed_skins'] ?? null) ? $appearanceRuntime['closed_skins'] : []);
    $lockedSkin = (string) ($lockedConfig['skin'] ?? 'default');
    $lockedSkinHasFixedScheme = array_key_exists($lockedSkin, $closedSkins);

    $requestedThemeToggle = array_key_exists('status_bar_show_theme_toggle', $scope)
        ? (bool) $scope['status_bar_show_theme_toggle']
        : true;
    $showThemeToggle = $requestedThemeToggle && $adminCustomizerEnabled && !$lockedSkinHasFixedScheme;
    $themeToggleAttribute = trim((string) ($scope['status_bar_theme_toggle_attribute'] ?? 'data-migrationui-theme-toggle'));
    if ($themeToggleAttribute === '' || preg_match('/^[a-zA-Z_:][a-zA-Z0-9:._-]*$/', $themeToggleAttribute) !== 1) {
        $themeToggleAttribute = 'data-migrationui-theme-toggle';
    }

    $themeToggleIconClass = trim((string) ($scope['status_bar_theme_toggle_icon_class'] ?? 'ti ti-circle-half-2'));
    if ($themeToggleIconClass === '') {
        $themeToggleIconClass = 'ti ti-circle-half-2';
    }

    $requestedCustomizerToggle = array_key_exists('status_bar_show_customizer_toggle', $scope)
        ? (bool) $scope['status_bar_show_customizer_toggle']
        : true;
    $showCustomizerToggle = $requestedCustomizerToggle && $adminCustomizerEnabled;
    $customizerToggleAttribute = trim((string) ($scope['status_bar_customizer_toggle_attribute'] ?? ''));
    if ($customizerToggleAttribute !== '' && preg_match('/^[a-zA-Z_:][a-zA-Z0-9:._-]*$/', $customizerToggleAttribute) !== 1) {
        $customizerToggleAttribute = '';
    }

    $customizerToggleIconClass = trim((string) ($scope['status_bar_customizer_toggle_icon_class'] ?? 'ti ti-settings'));
    if ($customizerToggleIconClass === '') {
        $customizerToggleIconClass = 'ti ti-settings';
    }

    $customizerToggleAriaLabel = trim((string) ($scope['status_bar_customizer_toggle_aria_label'] ?? 'Open theme customizer'));
    if ($customizerToggleAriaLabel === '') {
        $customizerToggleAriaLabel = 'Open theme customizer';
    }

    $customizerToggleTitle = trim((string) ($scope['status_bar_customizer_toggle_title'] ?? 'Admin Customizer'));
    if ($customizerToggleTitle === '') {
        $customizerToggleTitle = 'Admin Customizer';
    }
    $statusBarAssetVersion = (int) (@filemtime(PD . '/public/assets/js/catalyst/modules/status-bar.js') ?: time());
    $wsHost = strtolower(trim((string) ($wsCfg['ws_host'] ?? '')));
    $wsPort = (int) ($wsCfg['ws_port'] ?? 8080);
    $wsAvailable = (bool) ($wsCfg['enabled'] ?? true)
        && $wsHost !== ''
        && !in_array($wsHost, ['127.0.0.1', '0.0.0.0', 'localhost', '::1'], true);

    $authName = trim((string) ($authUser['name'] ?? ''));
    $authEmail = trim((string) ($authUser['email'] ?? ''));
    $loginHref = RedirectTarget::loginUrl($currentUri);

    $wsUrl = '';
    if ($wsAvailable) {
        $appScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        $wsScheme = strtolower((string) $appScheme) === 'http' ? 'ws' : 'wss';
        $isDefaultPort = ($wsScheme === 'ws' && $wsPort === 80) || ($wsScheme === 'wss' && $wsPort === 443);
        $portSuffix = $isDefaultPort ? '' : ':' . $wsPort;
        $wsUrl = sprintf('%s://%s%s/ws', $wsScheme, $wsHost, $portSuffix);
    }

    $wsConfig = [
        'isAuth' => $isAuth,
        'unread' => $isAuth ? $unreadCount : 0,
        'userId' => $isAuth ? (int) ($authUser['id'] ?? 0) : null,
        'tenantId' => $isAuth ? (int) ($authUser['tenant_id'] ?? 0) : null,
        'tenantKey' => $isAuth ? (string) ($authUser['tenant_key'] ?? '') : '',
        'i18n' => [
            'guest' => __('ui.status_bar.guest'),
            'authenticated_prefix' => __('ui.status_bar.authenticated_prefix'),
            'realtime_unavailable' => __('ui.status_bar.realtime_unavailable'),
            'connecting' => __('ui.status_bar.connecting'),
            'disconnected' => __('ui.status_bar.disconnected'),
            'error' => __('ui.status_bar.error'),
            'connected' => __('ui.status_bar.connected'),
            'auth_failed' => __('ui.status_bar.auth_failed'),
            'reconnecting' => __('ui.status_bar.reconnecting'),
            'refreshing' => __('ui.status_bar.refreshing'),
            'session_expired' => __('ui.status_bar.session_expired'),
            'loading' => __('ui.status_bar.loading'),
            'failed_to_load' => __('ui.status_bar.failed_to_load'),
            'unread_suffix' => __('ui.status_bar.unread_suffix'),
        ],
    ];

    if ($isAuth) {
        $wsConfig['token'] = $wsToken;
        $wsConfig['url'] = $wsUrl;
        $wsConfig['wsAvailable'] = $wsAvailable;
    }

    return [
        'csp_nonce' => CspNonce::get(),
        'logout_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'is_auth' => $isAuth,
        'show_theme_toggle' => $showThemeToggle,
        'theme_toggle_attribute' => $themeToggleAttribute,
        'theme_toggle_icon_class' => $themeToggleIconClass,
        'show_customizer_toggle' => $showCustomizerToggle,
        'customizer_toggle_attribute' => $customizerToggleAttribute,
        'customizer_toggle_icon_class' => $customizerToggleIconClass,
        'customizer_toggle_aria_label' => $customizerToggleAriaLabel,
        'customizer_toggle_title' => $customizerToggleTitle,
        'status_bar_context' => preg_replace('/[^a-z0-9_-]/i', '', (string) ($scope['status_bar_context'] ?? 'global')) ?: 'global',
        'status_text' => $isAuth ? __('ui.status_bar.authenticated_pending') : __('ui.status_bar.guest'),
        'account_menu_aria_label' => $isAuth ? __('ui.status_bar.open_account_menu') : __('ui.status_bar.open_session_menu'),
        'account_menu_title' => $isAuth ? __('ui.status_bar.account_menu') : __('ui.status_bar.session_menu'),
        'login_href' => $loginHref,
        'unread_count' => $unreadCount,
        'show_notifications' => $isAuth,
        'notification_badge_hidden' => $unreadCount === 0,
        'notification_badge_label' => $unreadCount > 99 ? '99+' : (string) $unreadCount,
        'notification_panel_count' => $unreadCount > 0 ? sprintf('(%d)', $unreadCount) : '',
        'account_name' => $authName !== '' ? $authName : ($authEmail !== '' ? $authEmail : __('ui.status_bar.account')),
        'account_role' => trim((string) ($authUser['role'] ?? '')),
        'has_account_role' => trim((string) ($authUser['role'] ?? '')) !== '',
        'status_bar_asset_version' => $statusBarAssetVersion,
        'ws_config_json' => TrustedHtml::fromString(InlineJson::encode($wsConfig)),
    ];
};
