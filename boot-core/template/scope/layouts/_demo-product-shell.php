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

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Navigation\AdminShellNavigationPresenter;
use Catalyst\Framework\Navigation\NavigationRegistry;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CspNonce;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $currentUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $currentPath = (string) (parse_url($currentUri, PHP_URL_PATH) ?: '/');
    $appearanceManager = PlatformAppearanceManager::getInstance();
    $appearanceRuntime = $appearanceManager->runtimeViewModel();
    $adminCustomizerEnabled = !array_key_exists('adminCustomizerEnabled', $appearanceRuntime)
        || (bool) $appearanceRuntime['adminCustomizerEnabled'];
    $authUser = AuthManager::getInstance()->user() ?? [];

    $styleLinks = [];
    $scriptLinks = [];
    $metaTags = [];

    foreach ((array) ($scope['meta'] ?? []) as $name => $content) {
        $metaTags[] = [
            'name' => (string) $name,
            'content' => (string) $content,
        ];
    }

    $appendStyle = static function (string $href, string $rel = 'stylesheet', string $media = '') use (&$styleLinks): void {
        foreach ($styleLinks as $existing) {
            if (($existing['href'] ?? '') === $href) {
                return;
            }
        }
        $styleLinks[] = [
            'href' => $href,
            'rel' => $rel,
            'has_media' => $media !== '',
            'media' => $media,
        ];
    };

    foreach ((array) ($scope['styles'] ?? []) as $style) {
        if (is_array($style)) {
            $media = trim((string) ($style['media'] ?? ''));
            $appendStyle((string) ($style['href'] ?? ''), (string) ($style['rel'] ?? 'stylesheet'), $media);
            continue;
        }
        $appendStyle((string) $style);
    }

    $appendScript = static function (string $src, bool $defer = false, bool $async = false, string $type = '', string $nonce = '') use (&$scriptLinks): void {
        foreach ($scriptLinks as $existing) {
            if (($existing['src'] ?? '') === $src) {
                return;
            }
        }
        $scriptLinks[] = [
            'src' => $src,
            'has_type' => $type !== '',
            'type' => $type,
            'defer' => $defer,
            'async' => $async,
            'has_nonce' => $nonce !== '',
            'nonce' => $nonce,
        ];
    };

    foreach ((array) ($scope['scripts'] ?? []) as $script) {
        if (is_array($script)) {
            $appendScript(
                (string) ($script['src'] ?? ''),
                !empty($script['defer']),
                !empty($script['async']),
                trim((string) ($script['type'] ?? '')),
                trim((string) ($script['nonce'] ?? ''))
            );
            continue;
        }
        $appendScript((string) $script);
    }

    $demoCssPath = implode(DS, [PD, 'public', 'assets', 'css', 'work', 'demoui', 'style.css']);
    $demoJsPath = implode(DS, [PD, 'public', 'assets', 'js', 'work', 'demoui', 'script.js']);
    $demoCssVersion = (string) (@filemtime($demoCssPath) ?: time());
    $demoJsVersion = (string) (@filemtime($demoJsPath) ?: time());
    $moduleSlug = trim((string) ($scope['moduleSlug'] ?? ''));

    // Compatibility only: legacy Framework/App surfaces still contain fa-* classes.
    // The canonical shell itself uses Inspinia/Tabler, but this keeps old view icons visible during cutover.
    $appendStyle('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
    $appendStyle('/assets/css/catalyst/status-bar.css');
    $appendStyle('/assets/css/work/demoui/style.css?v=' . rawurlencode($demoCssVersion));
    $appendStyle('/assets/css/catalyst/inspinia-product-cutover.css?v=' . rawurlencode((string) (@filemtime(implode(DS, [PD, 'public', 'assets', 'css', 'catalyst', 'inspinia-product-cutover.css'])) ?: time())));
    $appendStyle('/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css');
$appendStyle('/assets/css/catalyst/inspinia-runtime-compat.css?v=' . rawurlencode((string) (@filemtime(implode(DS, [PD, 'public', 'assets', 'css', 'catalyst', 'inspinia-runtime-compat.css'])) ?: time())));
    $appendStyle('/assets/css/catalyst/admin-surfaces.css?v=' . rawurlencode((string) (@filemtime(implode(DS, [PD, 'public', 'assets', 'css', 'catalyst', 'admin-surfaces.css'])) ?: time())));
    $appendStyle('/assets/css/catalyst/humanitarian-red-theme.css?v=' . rawurlencode((string) (@filemtime(implode(DS, [PD, 'public', 'assets', 'css', 'catalyst', 'humanitarian-red-theme.css'])) ?: time())));
    $appendScript('/assets/js/work/demoui/script.js?v=' . rawurlencode($demoJsVersion), true);

    if ($moduleSlug !== '' && $moduleSlug !== 'demoui') {
        $moduleCssPath = implode(DS, [PD, 'public', 'assets', 'css', 'work', $moduleSlug, 'style.css']);
        $moduleJsPath = implode(DS, [PD, 'public', 'assets', 'js', 'work', $moduleSlug, 'script.js']);

        if (file_exists($moduleCssPath)) {
            $appendStyle('/assets/css/work/' . rawurlencode($moduleSlug) . '/style.css?v=' . rawurlencode((string) (@filemtime($moduleCssPath) ?: time())));
        }

        if (file_exists($moduleJsPath)) {
            $appendScript(
                '/assets/js/work/' . rawurlencode($moduleSlug) . '/script.js?v=' . rawurlencode((string) (@filemtime($moduleJsPath) ?: time())),
                true,
                false,
                'module'
            );
        }
    }

    $matches = static function (array $paths) use ($currentPath): bool {
        foreach ($paths as $path) {
            $path = (string) $path;
            if ($path === '/') {
                if ($currentPath === '/') {
                    return true;
                }
                continue;
            }
            if ($currentPath === $path || str_starts_with($currentPath, $path . '/')) {
                return true;
            }
        }
        return false;
    };

    $makeItem = static function (string $label, string $href, string $icon, array $aliases = [], bool $prefixMatch = true) use ($currentPath): array {
        $paths = array_values(array_unique(array_merge([$href], $aliases)));
        $active = false;
        foreach ($paths as $path) {
            $path = (string) $path;
            if ($path === '/') {
                $active = $active || $currentPath === '/';
                continue;
            }

            $active = $active || $currentPath === $path || ($prefixMatch && str_starts_with($currentPath, $path . '/'));
        }
        return [
            'label' => $label,
            'href' => $href,
            'icon' => $icon,
            'is_active' => $active,
            'link_class' => $active ? 'side-nav-link active' : 'side-nav-link',
            'is_nested_collapse' => false,
            'badge_label' => '',
            'badge_class' => '',
        ];
    };

    $demoSections = [
        'base-ui' => [
            $makeItem('Accordions', '/demo-ui/accordions', 'ti ti-point'),
            $makeItem('Alerts', '/demo-ui/alerts', 'ti ti-point', ['/demo-ui']),
            $makeItem('Images', '/demo-ui/images', 'ti ti-point'),
            $makeItem('Badges', '/demo-ui/badges', 'ti ti-point'),
            $makeItem('Breadcrumb', '/demo-ui/breadcrumb', 'ti ti-point'),
            $makeItem('Buttons', '/demo-ui/buttons', 'ti ti-point'),
            $makeItem('Cards', '/demo-ui/cards', 'ti ti-point'),
            $makeItem('Carousel', '/demo-ui/carousel', 'ti ti-point'),
            $makeItem('Collapse', '/demo-ui/collapse', 'ti ti-point'),
            $makeItem('Colors', '/demo-ui/colors', 'ti ti-point'),
            $makeItem('Dropdowns', '/demo-ui/dropdowns', 'ti ti-point'),
            $makeItem('Videos', '/demo-ui/videos', 'ti ti-point'),
            $makeItem('Grid Options', '/demo-ui/grid-options', 'ti ti-point'),
            $makeItem('Links', '/demo-ui/links', 'ti ti-point'),
            $makeItem('List Group', '/demo-ui/list-group', 'ti ti-point'),
            $makeItem('Modals', '/demo-ui/modals', 'ti ti-point'),
            $makeItem('Notifications', '/demo-ui/notifications', 'ti ti-point'),
            $makeItem('Offcanvas', '/demo-ui/offcanvas', 'ti ti-point'),
            $makeItem('Placeholders', '/demo-ui/placeholders', 'ti ti-point'),
            $makeItem('Pagination', '/demo-ui/pagination', 'ti ti-point'),
            $makeItem('Popovers', '/demo-ui/popovers', 'ti ti-point'),
            $makeItem('Progress', '/demo-ui/progress', 'ti ti-point'),
            $makeItem('Scrollspy', '/demo-ui/scrollspy', 'ti ti-point'),
            $makeItem('Spinners', '/demo-ui/spinners', 'ti ti-point'),
            $makeItem('Tabs', '/demo-ui/tabs', 'ti ti-point'),
            $makeItem('Tooltips', '/demo-ui/tooltips', 'ti ti-point'),
            $makeItem('Typography', '/demo-ui/typography', 'ti ti-point'),
            $makeItem('Utilities', '/demo-ui/utilities', 'ti ti-point'),
        ],
        'charts' => [
            $makeItem('Apex Charts', '/demo-ui/charts/apex/area', 'ti ti-point'),
            $makeItem('Echarts', '/demo-ui/charts/echart/line', 'ti ti-point'),
        ],
        'forms' => [
            $makeItem('Basic Elements', '/demo-ui/basic-elements', 'ti ti-point'),
            $makeItem('Pickers', '/demo-ui/pickers', 'ti ti-point'),
            $makeItem('Select', '/demo-ui/select', 'ti ti-point'),
            $makeItem('Validation', '/demo-ui/validation', 'ti ti-point'),
            $makeItem('Wizard', '/demo-ui/wizard', 'ti ti-point'),
            $makeItem('File Uploads', '/demo-ui/file-uploads', 'ti ti-point'),
            $makeItem('Text Editors', '/demo-ui/text-editors', 'ti ti-point'),
            $makeItem('Range Slider', '/demo-ui/range-slider', 'ti ti-point'),
        ],
        'tables' => [
            $makeItem('Static Tables', '/demo-ui/tables/static', 'ti ti-point'),
            $makeItem('Custom Tables', '/demo-ui/tables/custom', 'ti ti-point'),
            $makeItem('DataTables', '/demo-ui/tables/datatables/basic', 'ti ti-point'),
        ],
    ];

    $showDemoComponents = $currentPath === '/demo-ui' || str_starts_with($currentPath, '/demo-ui/');

    $registryShell = NavigationRegistry::getInstance()->adminShell($currentPath, $authUser !== [] ? $authUser : null);
    $navGroups = AdminShellNavigationPresenter::fromAdminShell($registryShell);
    $definitions = [];

    if ($showDemoComponents) {
        $definitions[] = ['kind' => 'title', 'label' => 'Components'];
        $definitions[] = ['kind' => 'collapse', 'key' => 'base-ui', 'label' => 'Base UI', 'icon' => 'ti ti-diamonds'];
        $definitions[] = ['kind' => 'collapse', 'key' => 'charts', 'label' => 'Charts', 'icon' => 'ti ti-chart-donut'];
        $definitions[] = ['kind' => 'collapse', 'key' => 'forms', 'label' => 'Forms', 'icon' => 'ti ti-clipboard-text'];
        $definitions[] = ['kind' => 'collapse', 'key' => 'tables', 'label' => 'Tables', 'icon' => 'ti ti-table-options'];
    }

    foreach ($definitions as $definition) {
        if (($definition['kind'] ?? '') === 'title') {
            $navGroups[] = [
                'is_title' => true,
                'label' => (string) ($definition['label'] ?? ''),
            ];
            continue;
        }

        $key = (string) ($definition['key'] ?? '');
        $items = $demoSections[$key] ?? [];
        $isActive = false;
        foreach ($items as $item) {
            $isActive = $isActive || !empty($item['is_active']);
        }

        $navGroups[] = [
            'is_title' => false,
            'is_link' => false,
            'is_collapse' => true,
            'key' => $key,
            'label' => (string) ($definition['label'] ?? ''),
            'icon' => (string) ($definition['icon'] ?? 'ti ti-point'),
            'collapse_id' => 'demo-' . $key,
            'is_active' => $isActive,
            'expanded' => $isActive ? 'true' : 'false',
            'show' => $isActive,
            'items' => $items,
        ];
    }

    $authName = trim((string) ($scope['auth_name'] ?? ($authUser['name'] ?? '')));
    $authEmail = trim((string) ($scope['auth_email'] ?? ($authUser['email'] ?? '')));

    return [
        'document_title' => (string) ($scope['document_title'] ?? (($scope['title'] ?? 'Catalyst') . ' - Catalyst')),
        'lang' => (string) ($scope['lang'] ?? 'en'),
        'meta_tags' => $metaTags,
        'style_links' => $styleLinks,
        'script_links' => $scriptLinks,
        'branding' => is_array($scope['branding'] ?? null) ? $scope['branding'] : [],
        'auth_name' => $authName !== '' ? $authName : __('ui.product_nav.fallback_user'),
        'auth_email' => $authEmail,
        'has_auth_email' => $authEmail !== '',
        'auth_menu_label' => (string) ($scope['auth_menu_label'] ?? __('ui.product_nav.account_toggle')),
        'auth_role' => trim((string) ($scope['auth_role'] ?? ($authUser['role'] ?? 'guest'))),
        'auth_avatar_src' => (string) ($scope['auth_avatar_src'] ?? '/assets/vendor/inspinia/images/users/user-1.jpg'),
        'logout_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'csp_nonce' => (string) ($scope['csp_nonce'] ?? CspNonce::get()),
        'platform_appearance_json' => TrustedHtml::fromString(InlineJson::encode($appearanceRuntime)),
        'active_admin_context' => (string) ($scope['active_admin_context'] ?? ''),
        'breadcrumb_items' => is_array($scope['breadcrumb_items'] ?? null) ? $scope['breadcrumb_items'] : [],
        'has_breadcrumbs' => !empty($scope['has_breadcrumbs']),
        'demo_ui_nav_groups' => is_array($scope['demo_ui_nav_groups'] ?? null) && $scope['demo_ui_nav_groups'] !== [] ? $scope['demo_ui_nav_groups'] : $navGroups,
        'selected_doc_file' => (string) ($scope['selected_doc_file'] ?? ''),
        'selected_doc_label' => (string) ($scope['selected_doc_label'] ?? ''),
        'selected_doc_section' => (string) ($scope['selected_doc_section'] ?? ''),
        'selected_doc_source_url' => (string) ($scope['selected_doc_source_url'] ?? '/demo-ui'),
        'demo_ui_page_slug' => (string) ($scope['demo_ui_page_slug'] ?? trim($currentPath, '/')),
        'status_bar_show_theme_toggle' => $adminCustomizerEnabled && (array_key_exists('status_bar_show_theme_toggle', $scope)
            ? (bool) $scope['status_bar_show_theme_toggle']
            : true),
        'status_bar_theme_toggle_attribute' => (string) ($scope['status_bar_theme_toggle_attribute'] ?? 'data-demoui-theme-toggle'),
        'status_bar_theme_toggle_icon_class' => (string) ($scope['status_bar_theme_toggle_icon_class'] ?? 'ti ti-moon'),
        'status_bar_show_customizer_toggle' => $adminCustomizerEnabled && (array_key_exists('status_bar_show_customizer_toggle', $scope)
            ? (bool) $scope['status_bar_show_customizer_toggle']
            : true),
        'status_bar_customizer_toggle_attribute' => (string) ($scope['status_bar_customizer_toggle_attribute'] ?? 'data-theme-customizer-toggle'),
        'status_bar_customizer_toggle_icon_class' => (string) ($scope['status_bar_customizer_toggle_icon_class'] ?? 'ti ti-settings'),
        'status_bar_customizer_toggle_aria_label' => (string) ($scope['status_bar_customizer_toggle_aria_label'] ?? 'Open Admin Customizer'),
        'status_bar_customizer_toggle_title' => (string) ($scope['status_bar_customizer_toggle_title'] ?? 'Admin Customizer'),
        'suppress_work_assets' => true,
    ];
};
