<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ShellArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testNavigationUsesTheNeutralShellContract(): void
    {
        $registry = $this->read('app/Framework/Navigation/NavigationRegistry.php');
        $presenter = $this->read('app/Framework/Navigation/ShellNavigationPresenter.php');
        $provider = $this->read('app/Framework/Navigation/FrameworkNavigationProvider.php');
        $scope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains('public function shell(', $registry);
        Assert::contains("\$module['navigation']['shell']", $registry);
        Assert::contains('hasShellAuthorizationRule(', $registry);
        Assert::contains('final class ShellNavigationPresenter', $presenter);
        Assert::contains('if (!is_array($registryItem))', $presenter);
        Assert::contains('NavigationRegistry::getInstance()->shell(', $provider);
        Assert::contains('ShellNavigationPresenter::fromShell(', $provider);
        Assert::contains('NavigationModelSelector::getInstance()->select(', $scope);
        Assert::false(str_contains($registry, 'adminShell'));
        Assert::false(str_contains($scope, 'AdminShell'));
    }

    public function testShellRuntimeAndQualityGatesUseNeutralNames(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $navigation = $this->read('public/assets/js/catalyst/shell/navigation.js');
        $linter = $this->read('app/Framework/Module/ModuleLinter.php');

        Assert::contains("'body_class' => (string) (\$scope['body_class'] ?? 'catalyst-shell-body')", $scope);
        Assert::contains("html.setAttribute('data-sidenav-size', 'offcanvas')", $navigation);
        Assert::false(str_contains($navigation, 'catalyst-shell-sidebar-size'));
        Assert::contains('shell-navigation-not-registry-driven', $linter);
        Assert::false(str_contains($scope, 'active_admin_context'));
        Assert::false(str_contains($navigation, 'catalyst-admin-sidebar-size'));
        Assert::false(str_contains($linter, 'admin-shell-navigation-not-registry-driven'));
    }

    public function testSidebarUsesOneRecursiveNodeRenderer(): void
    {
        $sidebar = $this->read('boot-core/template/_sidebar.phtml');
        $node = $this->read('boot-core/template/_sidebar-node.phtml');
        $normalizer = $this->read('app/Framework/Navigation/NavigationTreeNormalizer.php');

        Assert::contains('{{> "./_sidebar-node" }}', $sidebar);
        Assert::contains('{{> "./_sidebar-node" }}', $node);
        Assert::contains('{{#each children}}', $node);
        Assert::false(str_contains($sidebar, 'is_nested_collapse'));
        Assert::false(str_contains($node, 'is_nested_collapse'));
        Assert::false(str_contains($sidebar, 'has_children'));
        Assert::false(str_contains($node, 'has_children'));
        Assert::false(str_contains($normalizer, "['items']"));
        Assert::false(str_contains($normalizer, 'is_collapse'));
        Assert::false(str_contains($normalizer, 'is_nested_collapse'));
        Assert::false(str_contains($normalizer, 'has_children'));
        Assert::false(str_contains($normalizer, "\$kind === 'collapse'"));
    }

    public function testIncludedNavigationUsesLocalizationCatalogs(): void
    {
        $registry = $this->read('app/Framework/Navigation/NavigationRegistry.php');
        $presenter = $this->read('app/Framework/Navigation/ShellNavigationPresenter.php');
        $demoUi = $this->read('app/Framework/Navigation/DemoUiNavigationProvider.php');

        Assert::contains("'label_key' => 'ui.product_nav.groups.configuration'", $registry);
        Assert::contains("'label_key' => 'ui.product_nav.items.environment_setup'", $presenter);
        Assert::contains("__('ui.product_nav.groups.configuration')", $demoUi);
        Assert::false(str_contains($presenter, "'badge_label' => \$runtimeAvailable ? '' : 'Disconnected'"));
    }

    public function testDocumentScopeUsesSemanticNavigationModels(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains('NavigationModelSelector::getInstance()->select(', $scope);
        Assert::contains('DemoUiNavigationProvider::ID', $scope);
        Assert::contains('FrameworkNavigationProvider::ID', $scope);
        Assert::contains('ApplicationNavigationProvider::ID', $scope);
        Assert::false(str_contains($scope, "\$scope['navigation_groups']"));
        Assert::false(str_contains($scope, "\$scope['account_nav_groups']"));
    }

    public function testActiveModuleManifestsDoNotDeclareAdminNavigationBuckets(): void
    {
        foreach ($this->phpFiles('Repository') as $file) {
            if (basename($file) !== 'module.php') {
                continue;
            }

            $source = file_get_contents($file);
            if (!is_string($source)) {
                throw new \RuntimeException("Unable to read {$file}.");
            }

            Assert::false(
                str_contains($source, "'navigation' => [\n        'admin' =>"),
                "Legacy navigation.admin bucket remains in {$file}."
            );
        }
    }

    public function testShellMigrationPreservesEverySupportedTheme(): void
    {
        $appearance = $this->read('app/Framework/Appearance/PlatformAppearanceManager.php');
        $bootstrap = $this->read('public/assets/js/catalyst/appearance-bootstrap.js');

        foreach ([
            'default',
            'minimal',
            'modern',
            'material',
            'pixel',
            'luxe',
            'flat',
            'red-cross',
            'civil-protection',
            'firefighters',
            'grempa',
        ] as $skin) {
            Assert::contains("'{$skin}'", $appearance);
        }

        Assert::contains("html.setAttribute('data-skin'", $bootstrap);
    }

    public function testGenericComponentCssLoadsBeforeThemeOverrides(): void
    {
        $assets = $this->read('boot-core/template/_head-assets.phtml');
        $head = $this->read('boot-core/template/_head.phtml');
        $genericPosition = strpos($assets, '{{ inspinia_runtime_compat_asset_url }}');
        $statusPosition = strpos($assets, '{{ status_bar_asset_url }}');
        $referencePosition = strpos($assets, '{{ ui_reference_asset_url }}');
        $modulePosition = strpos($head, '{{#each style_links}}');
        $redCrossPosition = strpos($head, '{{ red_cross_theme_asset_url }}');
        $responsePosition = strpos($head, '{{ response_skins_asset_url }}');

        Assert::true(is_int($genericPosition) && is_int($statusPosition) && is_int($referencePosition));
        Assert::true(is_int($modulePosition) && is_int($redCrossPosition) && is_int($responsePosition));
        Assert::true($genericPosition < $statusPosition);
        Assert::true($statusPosition < $referencePosition);
        Assert::true($modulePosition < $redCrossPosition);
        Assert::true($redCrossPosition < $responsePosition);
        Assert::false(str_contains($assets, '{{ red_cross_theme_asset_url }}'));
        Assert::false(str_contains($assets, '{{ response_skins_asset_url }}'));
    }

    public function testCanonicalDocumentLoadsFontAwesomeFromLocalAssets(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $assets = $this->read('boot-core/template/_head-assets.phtml');
        $fontAwesome = $this->read('public/assets/vendor/fontawesome/css/all.min.css');

        Assert::contains(
            "AssetUrl::versioned('/assets/vendor/fontawesome/css/all.min.css')",
            $scope
        );
        Assert::contains('{{ font_awesome_asset_url }}', $assets);
        Assert::contains('../webfonts/', $fontAwesome);
        Assert::contains('.fa-database', $fontAwesome);
        Assert::contains('.fa-shield-halved', $fontAwesome);
        Assert::contains('.fa-vial-circle-check', $fontAwesome);
        Assert::false(str_contains($assets, 'fontawesome.com'));
        Assert::true(is_file($this->root . '/public/assets/vendor/fontawesome/LICENSE.txt'));
        Assert::true(is_file($this->root . '/public/assets/vendor/fontawesome/webfonts/fa-solid-900.woff2'));
        Assert::true(is_file($this->root . '/public/assets/vendor/fontawesome/webfonts/fa-brands-400.woff2'));
    }

    public function testCanonicalDocumentConsumesGlobalBrandingAssets(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $sidebar = $this->read('boot-core/template/_sidebar.phtml');
        $topbar = $this->read('boot-core/template/_topbar.phtml');
        $headAssets = $this->read('boot-core/template/_head-assets.phtml');

        Assert::contains("'brand_logo_light_url' =>", $scope);
        Assert::contains("'brand_logo_dark_url' =>", $scope);
        Assert::contains("'brand_logo_small_url' =>", $scope);
        Assert::contains("'favicon_asset_url' => AssetUrl::versioned((string) (\$branding['favicon_url']", $scope);
        Assert::contains('src="{{ brand_logo_light_url }}"', $sidebar);
        Assert::contains('src="{{ brand_logo_dark_url }}"', $sidebar);
        Assert::contains('src="{{ brand_logo_small_url }}"', $sidebar);
        Assert::contains('src="{{ brand_logo_light_url }}"', $topbar);
        Assert::contains('src="{{ brand_logo_dark_url }}"', $topbar);
        Assert::contains('src="{{ brand_logo_small_url }}"', $topbar);
        Assert::contains('href="{{ favicon_asset_url }}"', $headAssets);
        Assert::false(str_contains($sidebar, '/assets/vendor/inspinia/images/logo'));
        Assert::false(str_contains($topbar, '/assets/vendor/inspinia/images/logo'));
    }

    public function testInternalShellAssignsViewportScrollingToContentPage(): void
    {
        $compat = $this->read('public/assets/css/catalyst/inspinia-runtime-compat.css');
        $content = $this->read('boot-core/template/_content.phtml');

        Assert::contains('body.catalyst-shell-body {', $compat);
        Assert::contains('overflow: hidden;', $compat);
        Assert::contains('body.catalyst-shell-body > .wrapper {', $compat);
        Assert::contains('body.catalyst-shell-body .content-page {', $compat);
        Assert::contains('height: calc(100vh - var(--theme-topbar-height) - 32px);', $compat);
        Assert::contains('min-height: 0;', $compat);
        Assert::contains('overflow: visible;', $compat);
        Assert::contains('body.catalyst-shell-body .content-page[data-simplebar] {', $compat);
        Assert::contains('body.catalyst-shell-body .content-page[data-simplebar] > .simplebar-wrapper,', $compat);
        Assert::contains('body.catalyst-shell-body .content-page[data-simplebar] .simplebar-content {', $compat);
        Assert::contains('data-simplebar=""', $content);
    }

    public function testMobileSidebarRemainsOwnedByTheGlobalShellRuntime(): void
    {
        $navigation = $this->read('public/assets/js/catalyst/shell/navigation.js');
        $compat = $this->read('public/assets/css/catalyst/inspinia-runtime-compat.css');
        $topbar = $this->read('boot-core/template/_topbar.phtml');

        Assert::contains('data-shell-sidebar-toggle', $topbar);
        Assert::contains('<button type="button" class="logo-light', $topbar);
        Assert::false(str_contains($topbar, '<a href="{{ brand_home_href }}" class="logo-light" data-shell-sidebar-toggle'));
        Assert::contains("document.addEventListener('catalyst:ui:ready'", $navigation);
        Assert::contains('applyResponsiveState(false);', $navigation);
        Assert::contains("html.classList.toggle('sidebar-enable'", $navigation);
        Assert::contains('pointerdown', $navigation);
        Assert::contains('pointerup', $navigation);
        Assert::contains('catalyst-shell-sidebar-backdrop', $navigation);
        Assert::contains('@media (max-width: 991.98px)', $compat);
        Assert::contains('.catalyst-shell-sidebar-backdrop', $compat);
        Assert::false(str_contains($navigation, 'keepSidebarFixed'));
    }

    public function testAjaxFormRuntimeDiagnosesHtmlResponseMismatch(): void
    {
        $http = $this->read('public/assets/js/catalyst/core/http.js');
        $formHandler = $this->read('public/assets/js/catalyst/forms/form-handler.js');

        Assert::contains('Catalyst form contract mismatch:', $http);
        Assert::contains('expectedContentType: \'application/json\'', $formHandler);
    }

    /** @return list<string> */
    private function phpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->root . DIRECTORY_SEPARATOR . $directory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function read(string $path): string
    {
        $source = file_get_contents(
            $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
        );

        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
