<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Navigation\ApplicationNavigationProvider;
use Catalyst\Framework\Navigation\DemoUiNavigationProvider;
use Catalyst\Framework\Navigation\FrameworkNavigationProvider;
use Catalyst\Framework\Navigation\NavigationModelSelector;
use Catalyst\Framework\Navigation\NavigationRegistry;
use Catalyst\Framework\Session\FlashMessage;
use Catalyst\Framework\Session\ToastQueue;
use Catalyst\Helpers\Security\CsrfProtection;
use Catalyst\Helpers\Security\CspNonce;

/**
 * Prepares every complete HTML response for the canonical document template.
 *
 * Responsibility: Builds shared document, theme, shell, navigation and asset data without selecting layout profiles.
 */
final class DocumentScope
{
    /**
     * Prepares the canonical document scope.
     *
     * Responsibility: Applies document defaults while preserving explicit surface capabilities supplied by controllers.
     *
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public static function prepare(array $scope): array
    {
        $appearance = PlatformAppearanceManager::getInstance();
        $branding = $appearance->brandingViewModel();
        $runtime = $appearance->runtimeViewModel();
        $initial = is_array($runtime['lockedConfig'] ?? null)
            ? $runtime['lockedConfig']
            : (is_array($runtime['defaults'] ?? null) ? $runtime['defaults'] : []);
        $authUser = AuthManager::getInstance()->user() ?? [];
        $currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
        $brandName = (string) ($branding['brand_name'] ?? 'Catalyst');
        $title = (string) ($scope['title'] ?? $scope['pageTitle'] ?? $brandName);
        $showSidebar = self::boolScope($scope, 'show_sidebar', true);
        $breadcrumbItems = self::breadcrumbItems($scope, $showSidebar, $currentPath, $authUser);
        $initialState = self::initialState();
        $hasInitialState = $initialState['toasts'] !== []
            || $initialState['flash']['regular'] !== []
            || $initialState['flash']['persistent'] !== [];
        $csrfMetaTag = TrustedHtml::fromString(CsrfProtection::getInstance()->getMetaTag());
        $initialStateJson = TrustedHtml::fromString(InlineJson::encode($initialState));

        $defaults = [
            'document_title' => (string) ($scope['document_title'] ?? ($title !== $brandName ? $title . ' - ' . $brandName : $title)),
            'lang' => (string) ($scope['lang'] ?? 'en'),
            'html_direction' => (string) ($scope['html_direction'] ?? $initial['dir'] ?? 'ltr'),
            'html_theme' => (string) ($scope['html_theme'] ?? $initial['theme'] ?? 'light'),
            'html_skin' => (string) ($scope['html_skin'] ?? $initial['skin'] ?? 'default'),
            'html_layout_width' => (string) ($scope['html_layout_width'] ?? $initial['width'] ?? 'fluid'),
            'html_layout_position' => (string) ($scope['html_layout_position'] ?? $initial['position'] ?? 'fixed'),
            'html_menu_color' => (string) ($scope['html_menu_color'] ?? $initial['sidenav-color'] ?? 'dark'),
            'html_sidenav_size' => (string) ($scope['html_sidenav_size'] ?? $initial['sidenav-size'] ?? 'default'),
            'html_topbar_color' => (string) ($scope['html_topbar_color'] ?? $initial['topbar-color'] ?? 'gray'),
            'meta_tags' => self::metaTags($scope),
            'style_links' => self::styleLinks($scope),
            'script_links' => self::scriptLinks($scope),
            'csp_nonce' => (string) ($scope['csp_nonce'] ?? CspNonce::get()),
            'platform_appearance_json' => TrustedHtml::fromString(InlineJson::encode($runtime)),
            'favicon_asset_url' => AssetUrl::versioned((string) ($branding['favicon_url'] ?? '/assets/vendor/inspinia/images/favicon.ico')),
            'appearance_bootstrap_asset_url' => AssetUrl::versioned('/assets/js/catalyst/appearance-bootstrap.js'),
            'inspinia_vendors_asset_url' => AssetUrl::versioned('/assets/vendor/inspinia/css/vendors.min.css'),
            'font_awesome_asset_url' => AssetUrl::versioned('/assets/vendor/fontawesome/css/all.min.css'),
            'datatables_responsive_asset_url' => AssetUrl::versioned('/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css'),
            'inspinia_app_asset_url' => AssetUrl::versioned('/assets/vendor/inspinia/css/app.min.css'),
            'red_cross_theme_asset_url' => AssetUrl::versioned('/assets/css/catalyst/red-cross-theme.css'),
            'response_skins_asset_url' => AssetUrl::versioned('/assets/css/catalyst/response-skins.css'),
            'notifications_asset_url' => AssetUrl::versioned('/assets/css/catalyst/notifications.css'),
            'activity_overlay_asset_url' => AssetUrl::versioned('/assets/css/catalyst/activity-overlay.css'),
            'inspinia_runtime_compat_asset_url' => AssetUrl::versioned('/assets/css/catalyst/inspinia-runtime-compat.css'),
            'datagrid_asset_url' => AssetUrl::versioned('/assets/css/catalyst/datagrid.css'),
            'form_builder_asset_url' => AssetUrl::versioned('/assets/css/catalyst/form-builder.css'),
            'record_presence_asset_url' => AssetUrl::versioned('/assets/css/catalyst/record-presence.css'),
            'surfaces_asset_url' => AssetUrl::versioned('/assets/css/catalyst/surfaces.css'),
            'status_bar_asset_url' => AssetUrl::versioned('/assets/css/catalyst/status-bar.css'),
            'ui_reference_asset_url' => AssetUrl::versioned('/assets/css/catalyst/ui-reference.css'),
            'bootstrap_bundle_asset_url' => AssetUrl::versioned('/assets/vendor/bootstrap/js/bootstrap.bundle.min.js'),
            'ui_runtime_asset_url' => AssetUrl::versionedTree(
                '/assets/js/catalyst/runtime/ui-runtime.js',
                '/assets/js/catalyst'
            ),
            'body_class' => (string) ($scope['body_class'] ?? 'catalyst-shell-body'),
            'surface_context' => (string) ($scope['surface_context'] ?? 'global'),
            'surface_page' => (string) ($scope['surface_page'] ?? trim($currentPath, '/')),
            'inspinia_document' => (string) ($scope['inspinia_document'] ?? $scope['selected_doc_file'] ?? ''),
            'show_topbar' => self::boolScope($scope, 'show_topbar', true),
            'show_sidebar' => $showSidebar,
            'show_status_bar' => self::boolScope($scope, 'show_status_bar', true),
            'show_theme_customizer' => self::boolScope($scope, 'show_theme_customizer', true),
            'show_auth_brand_panel' => self::boolScope($scope, 'show_auth_brand_panel', false),
            'is_public_surface' => self::boolScope($scope, 'is_public_surface', false),
            'is_account_surface' => self::boolScope($scope, 'is_account_surface', false),
            'is_account_guest' => self::boolScope($scope, 'is_account_guest', false),
            'is_error_surface' => self::boolScope($scope, 'is_error_surface', false),
            'shell_class' => (string) ($scope['shell_class'] ?? 'wrapper'),
            'topbar_class' => (string) ($scope['topbar_class'] ?? 'app-topbar'),
            'sidebar_class' => (string) ($scope['sidebar_class'] ?? 'sidenav-menu'),
            'sidebar_label' => (string) ($scope['sidebar_label'] ?? 'Catalyst navigation'),
            'content_class' => (string) ($scope['content_class'] ?? 'content-page'),
            'status_bar_class' => (string) ($scope['status_bar_class'] ?? 'catalyst-status-bar'),
            'status_bar_label' => (string) ($scope['status_bar_label'] ?? $brandName),
            'status_bar_context' => (string) ($scope['status_bar_context'] ?? $scope['surface_context'] ?? 'global'),
            'status_bar_show_theme_toggle' => self::boolScope($scope, 'status_bar_show_theme_toggle', true),
            'status_bar_show_customizer_toggle' => self::boolScope($scope, 'status_bar_show_customizer_toggle', true),
            'brand_home_href' => (string) ($scope['brand_home_href'] ?? '/dashboard'),
            'brand_name' => $brandName,
            'brand_logo_light_url' => (string) ($branding['logo_light_url'] ?? '/assets/vendor/inspinia/images/logo.png'),
            'brand_logo_dark_url' => (string) ($branding['logo_dark_url'] ?? '/assets/vendor/inspinia/images/logo-black.png'),
            'brand_logo_small_url' => (string) ($branding['logo_small_url'] ?? '/assets/vendor/inspinia/images/logo-sm.png'),
            'brand_tagline' => (string) ($branding['brand_tagline'] ?? ''),
            'has_brand_tagline' => trim((string) ($branding['brand_tagline'] ?? '')) !== '',
            'account_href' => (string) ($scope['account_href'] ?? '/account/profile'),
            'account_label' => (string) ($scope['account_label'] ?? 'Account'),
            'auth_name' => trim((string) ($scope['auth_name'] ?? $authUser['name'] ?? '')) ?: 'Guest',
            'auth_avatar_src' => (string) ($scope['auth_avatar_src'] ?? '/assets/vendor/inspinia/images/users/user-1.jpg'),
            'navigation_groups' => self::navigationGroups($scope, $showSidebar, $currentPath, $authUser),
            'breadcrumb_items' => $breadcrumbItems,
            'has_breadcrumbs' => $breadcrumbItems !== [],
            'public_navigation_items' => self::publicNavigation((array) ($scope['publicNavigation'] ?? [])),
            'has_error_ticket' => trim((string) ($scope['error_ticket'] ?? '')) !== '',
            'is_development' => defined('IS_DEVELOPMENT') && IS_DEVELOPMENT,
        ];

        $prepared = array_replace($defaults, $scope);
        $prepared = array_replace($prepared, TopbarViewModel::build($prepared), $scope);
        $prepared = array_replace($prepared, StatusBarViewModel::build($prepared), $scope);
        $prepared['csrf_meta_tag'] = $csrfMetaTag;
        $prepared['has_initial_state'] = $hasInitialState;
        $prepared['initial_state_json'] = $initialStateJson;
        $prepared['is_development'] = defined('IS_DEVELOPMENT') && IS_DEVELOPMENT;

        return $prepared;
    }

    /**
     * @return array{
     *     toasts: list<array{type: string, message: string}>,
     *     flash: array{
     *         regular: array<string, list<string>>,
     *         persistent: list<array{id: string, type: string, message: string}>
     *     }
     * }
     */
    private static function initialState(): array
    {
        $toasts = array_map(static fn (array $toast): array => [
            'type' => preg_replace('/[^a-z_]/i', '', (string) ($toast['type'] ?? 'info')) ?: 'info',
            'message' => (string) ($toast['message'] ?? ''),
        ], ToastQueue::getInstance()->all());
        $flash = FlashMessage::getInstance();

        return [
            'toasts' => array_values($toasts),
            'flash' => [
                'regular' => $flash->all(),
                'persistent' => $flash->allPersistent(),
            ],
        ];
    }

    /** @param array<string, mixed> $scope */
    private static function metaTags(array $scope): array
    {
        if (is_array($scope['meta_tags'] ?? null)) {
            return $scope['meta_tags'];
        }

        $tags = [];
        foreach ((array) ($scope['meta'] ?? []) as $name => $content) {
            $tags[] = ['name' => (string) $name, 'content' => (string) $content];
        }

        return $tags;
    }

    /** @param array<string, mixed> $scope */
    private static function styleLinks(array $scope): array
    {
        $links = is_array($scope['style_links'] ?? null) ? $scope['style_links'] : [];
        $capabilityAssets = [
            'is_account_surface' => '/assets/css/catalyst/account-shell.css',
            'show_auth_brand_panel' => '/assets/css/catalyst/auth.css',
            'is_public_surface' => '/assets/css/catalyst/public-shell.css',
            'is_error_surface' => '/assets/css/catalyst/error-surface.css',
        ];

        foreach ($capabilityAssets as $capability => $href) {
            if (!empty($scope[$capability])) {
                $links[] = ['href' => AssetUrl::versioned($href), 'rel' => 'stylesheet', 'has_media' => false];
            }
        }

        foreach ((array) ($scope['styles'] ?? []) as $style) {
            $links[] = is_array($style)
                ? [
                    'href' => AssetUrl::versioned((string) ($style['href'] ?? '')),
                    'rel' => (string) ($style['rel'] ?? 'stylesheet'),
                    'has_media' => !empty($style['media']),
                    'media' => (string) ($style['media'] ?? ''),
                ]
                : ['href' => AssetUrl::versioned((string) $style), 'rel' => 'stylesheet', 'has_media' => false];
        }

        $slug = trim((string) ($scope['moduleSlug'] ?? ''));
        if ($slug !== '' && empty($scope['suppress_work_assets'])) {
            $links[] = [
                'href' => AssetUrl::versioned("/assets/css/work/{$slug}/style.css"),
                'rel' => 'stylesheet',
                'has_media' => false,
            ];
        }

        return self::uniqueLinks($links, 'href');
    }

    /** @param array<string, mixed> $scope */
    private static function scriptLinks(array $scope): array
    {
        $links = is_array($scope['script_links'] ?? null) ? $scope['script_links'] : [];

        foreach ((array) ($scope['scripts'] ?? []) as $script) {
            $links[] = is_array($script)
                ? [
                    'src' => AssetUrl::versioned((string) ($script['src'] ?? '')),
                    'is_module' => ($script['type'] ?? '') === 'module',
                    'defer' => !empty($script['defer']),
                    'async' => !empty($script['async']),
                    'has_nonce' => !empty($script['nonce']),
                    'nonce' => (string) ($script['nonce'] ?? ''),
                ]
                : [
                    'src' => AssetUrl::versioned((string) $script),
                    'is_module' => false,
                    'defer' => false,
                    'async' => false,
                    'has_nonce' => false,
                ];
        }

        $slug = trim((string) ($scope['moduleSlug'] ?? ''));
        if ($slug !== '' && empty($scope['suppress_work_assets'])) {
            $links[] = [
                'src' => AssetUrl::versioned("/assets/js/work/{$slug}/script.js"),
                'is_module' => true,
                'defer' => true,
                'async' => false,
                'has_nonce' => false,
            ];
        }

        return self::uniqueLinks($links, 'src');
    }

    /**
     * @param array<string, mixed> $scope
     * @param array<string, mixed> $authUser
     */
    private static function navigationGroups(array $scope, bool $showSidebar, string $currentPath, array $authUser): array
    {
        if (!$showSidebar) {
            return [];
        }

        $model = self::navigationModel($scope, $currentPath);
        $context = array_replace(
            (array) ($scope['navigation_model_data'] ?? []),
            [
                'current_path' => $currentPath,
                'user' => $authUser !== [] ? $authUser : null,
            ]
        );

        return NavigationModelSelector::getInstance()->select($model, $context);
    }

    /**
     * Resolves the semantic navigation model for the current surface.
     *
     * @param array<string, mixed> $scope
     */
    private static function navigationModel(array $scope, string $currentPath): string
    {
        $explicit = trim((string) ($scope['navigation_model'] ?? ''));
        if ($explicit !== '') {
            return $explicit;
        }

        if ((string) ($scope['surface_context'] ?? '') === 'demo-ui'
            || str_starts_with($currentPath, '/demo-ui')
        ) {
            return DemoUiNavigationProvider::ID;
        }

        if (!empty($scope['is_account_surface'])) {
            return ApplicationNavigationProvider::ID;
        }

        return FrameworkNavigationProvider::ID;
    }

    private static function publicNavigation(array $items): array
    {
        return array_map(static fn (array $item): array => [
            'label' => (string) ($item['label'] ?? ''),
            'href' => (string) ($item['href'] ?? '#'),
            'link_class' => (string) ($item['link_class'] ?? ('catalyst-public-nav__link' . (!empty($item['is_active']) ? ' is-active' : ''))),
            'is_active' => !empty($item['is_active']),
        ], array_values(array_filter($items, 'is_array')));
    }

    /**
     * @param array<string, mixed> $scope
     * @param array<string, mixed> $authUser
     * @return list<array{label: string, href: string, is_active: bool}>
     */
    private static function breadcrumbItems(array $scope, bool $showSidebar, string $currentPath, array $authUser): array
    {
        $definitions = is_array($scope['breadcrumb_items'] ?? null)
            ? $scope['breadcrumb_items']
            : [];

        if ($definitions === [] && $showSidebar) {
            foreach (NavigationRegistry::getInstance()->breadcrumbs(
                $currentPath,
                $authUser !== [] ? $authUser : null
            ) as $label => $href) {
                $definitions[] = ['label' => $label, 'href' => $href];
            }
        }

        $items = [];
        foreach ($definitions as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $href = trim((string) ($item['href'] ?? ''));
            $items[] = [
                'label' => $label,
                'href' => $href,
                'is_active' => !empty($item['is_active']) || $href === '' || $index === array_key_last($definitions),
            ];
        }

        return $items;
    }

    /** @param array<string, mixed> $scope */
    private static function boolScope(array $scope, string $key, bool $default): bool
    {
        return array_key_exists($key, $scope) ? (bool) $scope[$key] : $default;
    }

    private static function uniqueLinks(array $links, string $key): array
    {
        $unique = [];
        foreach ($links as $link) {
            if (!is_array($link) || trim((string) ($link[$key] ?? '')) === '') {
                continue;
            }

            $unique[(string) $link[$key]] = $link;
        }

        return array_values($unique);
    }
}
