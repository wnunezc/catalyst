<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Notification\NotificationRepository;
use Catalyst\Framework\WebSocket\WebSocketToken;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Security\CspNonce;
use Catalyst\Helpers\Security\CsrfProtection;
use Throwable;

/**
 * Builds the canonical status bar view model without embedding PHP in templates.
 *
 * Responsibility: Restores the previous status bar capabilities while keeping
 * the new token-template layout clean and declarative.
 */
final class StatusBarViewModel
{
    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public static function build(array $scope): array
    {
        $auth = AuthManager::getInstance();
        $isAuth = $auth->check();
        $authUser = $isAuth ? ($auth->user() ?? []) : [];
        $authId = $isAuth ? (int) ($auth->id() ?? 0) : 0;
        $unreadCount = self::unreadCount($isAuth, $authId);
        $appearanceRuntime = PlatformAppearanceManager::getInstance()->runtimeViewModel();
        $customizerEnabled = (bool) ($appearanceRuntime['customizerEnabled']
            ?? $appearanceRuntime['customizer_enabled']
            ?? true);
        $lockedConfig = self::arrayValue($appearanceRuntime['lockedConfig'] ?? null)
            ?: self::arrayValue($appearanceRuntime['locked_config'] ?? null);
        $closedSkins = self::arrayValue($appearanceRuntime['closedSkins'] ?? null)
            ?: self::arrayValue($appearanceRuntime['closed_skins'] ?? null);
        $lockedSkin = (string) ($lockedConfig['skin'] ?? 'default');
        $lockedSkinHasFixedScheme = array_key_exists($lockedSkin, $closedSkins);

        $showThemeToggle = self::boolScope($scope, 'status_bar_show_theme_toggle', true)
            && $customizerEnabled
            && !$lockedSkinHasFixedScheme;
        $showCustomizerToggle = self::boolScope(
            $scope,
            'status_bar_show_customizer_toggle',
            !empty($scope['show_theme_customizer'])
        ) && $customizerEnabled;

        $currentUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $authName = trim((string) ($authUser['name'] ?? $scope['auth_name'] ?? ''));
        $authEmail = trim((string) ($authUser['email'] ?? ''));
        $accountRole = trim((string) ($authUser['role'] ?? $scope['auth_role'] ?? ''));
        $statusContext = self::safeContext((string) ($scope['status_bar_context'] ?? $scope['surface_context'] ?? 'global'));
        $wsConfig = self::webSocketConfig($isAuth, $authUser, $authId, $unreadCount);
        $notificationBadgeLabel = $unreadCount > 99 ? '99+' : (string) $unreadCount;

        return [
            'csp_nonce' => (string) ($scope['csp_nonce'] ?? CspNonce::get()),
            'logout_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
            'is_auth' => $isAuth,
            'show_theme_toggle' => $showThemeToggle,
            'theme_toggle_attribute' => TrustedHtml::fromString(self::safeAttribute(
                (string) ($scope['status_bar_theme_toggle_attribute'] ?? 'data-catalyst-theme-toggle'),
                'data-catalyst-theme-toggle'
            )),
            'theme_toggle_icon_class' => self::safeIconClass(
                (string) ($scope['status_bar_theme_toggle_icon_class'] ?? 'ti ti-circle-half-2'),
                'ti ti-circle-half-2'
            ),
            'show_customizer_toggle' => $showCustomizerToggle,
            'customizer_toggle_attribute' => TrustedHtml::fromString(self::safeAttribute(
                (string) ($scope['status_bar_customizer_toggle_attribute'] ?? 'data-theme-customizer-toggle'),
                'data-theme-customizer-toggle'
            )),
            'customizer_toggle_icon_class' => self::safeIconClass(
                (string) ($scope['status_bar_customizer_toggle_icon_class'] ?? 'ti ti-settings'),
                'ti ti-settings'
            ),
            'customizer_toggle_aria_label' => self::nonEmptyString(
                (string) ($scope['status_bar_customizer_toggle_aria_label'] ?? __('ui.status_bar.open_theme_customizer')),
                'Open theme customizer'
            ),
            'customizer_toggle_title' => self::nonEmptyString(
                (string) ($scope['status_bar_customizer_toggle_title'] ?? __('ui.status_bar.theme_customizer')),
                'Theme customizer'
            ),
            'status_bar_context' => $statusContext,
            'status_bar_class' => self::statusBarClass((string) ($scope['status_bar_class'] ?? 'catalyst-status-bar')),
            'status_text' => $isAuth ? __('ui.status_bar.authenticated_pending') : __('ui.status_bar.guest'),
            'status_bar_label' => $isAuth ? __('ui.status_bar.authenticated_pending') : __('ui.status_bar.guest'),
            'account_menu_aria_label' => $isAuth ? __('ui.status_bar.open_account_menu') : __('ui.status_bar.open_session_menu'),
            'account_menu_title' => $isAuth ? __('ui.status_bar.account_menu') : __('ui.status_bar.session_menu'),
            'login_href' => RedirectTarget::loginUrl($currentUri),
            'show_registration_link' => FeatureFlagManager::getInstance()->isRuntimeEnabled('auth.registration_enabled'),
            'unread_count' => $unreadCount,
            'show_notifications' => $isAuth,
            'notification_badge_hidden' => $unreadCount === 0,
            'notification_badge_label' => $notificationBadgeLabel,
            'notification_panel_count' => $unreadCount > 0 ? sprintf('(%d)', $unreadCount) : '',
            'account_name' => $authName !== '' ? $authName : ($authEmail !== '' ? $authEmail : __('ui.status_bar.account')),
            'account_role' => $accountRole,
            'has_account_role' => $accountRole !== '',
            'status_bar_asset_version' => self::assetVersion(['assets', 'js', 'catalyst', 'shell', 'status-bar.js']),
            'ws_config_json' => TrustedHtml::fromString(InlineJson::encode($wsConfig)),
        ];
    }

    private static function unreadCount(bool $isAuth, mixed $authId): int
    {
        if (!$isAuth) {
            return 0;
        }

        try {
            return max(0, NotificationRepository::getInstance()->countUnread((int) $authId));
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * @param array<string, mixed> $authUser
     * @return array<string, mixed>
     */
    private static function webSocketConfig(bool $isAuth, array $authUser, int $authId, int $unreadCount): array
    {
        $config = [
            'isAuth' => $isAuth,
            'unread' => $isAuth ? $unreadCount : 0,
            'userId' => $isAuth ? (int) ($authUser['id'] ?? $authId) : null,
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

        if (!$isAuth) {
            return $config;
        }

        $ws = self::webSocketEndpoint();
        $config['token'] = WebSocketToken::generate($authId, 3600);
        $config['url'] = $ws['url'];
        $config['wsAvailable'] = $ws['available'];

        return $config;
    }

    /**
     * @return array{available: bool, url: string}
     */
    private static function webSocketEndpoint(): array
    {
        $env = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];
        $appUrl = rtrim((string) ($env['APP_URL'] ?? ''), '/');
        $wsConfig = ConfigManager::getInstance()->entry('websocket', 'websocket');
        $wsHost = strtolower(trim((string) ($wsConfig['ws_host'] ?? '')));
        $wsPort = (int) ($wsConfig['ws_port'] ?? 8080);
        $available = (bool) ($wsConfig['enabled'] ?? true)
            && $wsHost !== ''
            && !in_array($wsHost, ['127.0.0.1', '0.0.0.0', 'localhost', '::1'], true);

        if (!$available) {
            return ['available' => false, 'url' => ''];
        }

        $appScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        $wsScheme = strtolower((string) $appScheme) === 'http' ? 'ws' : 'wss';
        $isDefaultPort = ($wsScheme === 'ws' && $wsPort === 80) || ($wsScheme === 'wss' && $wsPort === 443);
        $portSuffix = $isDefaultPort ? '' : ':' . $wsPort;

        return [
            'available' => true,
            'url' => sprintf('%s://%s%s/ws', $wsScheme, $wsHost, $portSuffix),
        ];
    }

    /** @return array<string, mixed> */
    private static function arrayValue(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /** @param array<string, mixed> $scope */
    private static function boolScope(array $scope, string $key, bool $default): bool
    {
        return array_key_exists($key, $scope) ? (bool) $scope[$key] : $default;
    }

    private static function safeAttribute(string $attribute, string $default): string
    {
        $attribute = trim($attribute);
        if ($attribute === '') {
            return $default;
        }

        return preg_match('/^[a-zA-Z_:][a-zA-Z0-9:._-]*$/', $attribute) === 1 ? $attribute : $default;
    }

    private static function safeIconClass(string $class, string $default): string
    {
        $class = trim($class);
        if ($class === '') {
            return $default;
        }

        return preg_match('/^[a-zA-Z0-9\s:_-]+$/', $class) === 1 ? $class : $default;
    }

    private static function safeContext(string $context): string
    {
        $context = preg_replace('/[^a-z0-9_-]/i', '', $context) ?: '';
        return $context !== '' ? $context : 'global';
    }

    private static function statusBarClass(string $class): string
    {
        $class = trim($class);
        return $class !== '' ? $class : 'catalyst-status-bar';
    }

    private static function nonEmptyString(string $value, string $fallback): string
    {
        $value = trim($value);
        return $value !== '' ? $value : $fallback;
    }

    /** @param list<string> $segments */
    private static function assetVersion(array $segments): int
    {
        return (int) (@filemtime(implode(DS, array_merge([PD, 'public'], $segments))) ?: time());
    }
}
