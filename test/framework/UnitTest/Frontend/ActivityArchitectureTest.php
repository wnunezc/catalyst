<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ActivityArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testCanonicalDocumentOwnsOneInitiallyVisibleActivityOverlay(): void
    {
        $document = $this->read('boot-core/template/document.phtml');
        $allTemplates = $this->readDirectory('boot-core/template', ['phtml']);

        Assert::same(1, substr_count($allTemplates, 'data-catalyst-activity-overlay'));
        Assert::contains('data-catalyst-activity-overlay', $document);
        Assert::contains('aria-live="polite"', $document);
        Assert::contains('aria-modal="true"', $document);
        Assert::contains('data-activity-state="booting"', $document);
        Assert::contains('{{#if is_development}}', $document);
        Assert::contains('data-catalyst-activity-release', $document);
    }

    public function testActivityOverlayCssIsLoadedCentrallyBeforeTheBodyRuntime(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $head = $this->read('boot-core/template/_head-assets.phtml');
        $css = $this->read('public/assets/css/catalyst/activity-overlay.css');

        Assert::contains("'activity_overlay_asset_url'", $scope);
        Assert::contains('/assets/css/catalyst/activity-overlay.css', $scope);
        Assert::contains('{{ activity_overlay_asset_url }}', $head);
        Assert::contains('[data-catalyst-activity-overlay]', $css);
        Assert::contains('pointer-events: all;', $css);
        Assert::contains('position: fixed;', $css);
    }

    public function testCanonicalRuntimeOwnsTheSingleActivityManager(): void
    {
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');
        $activity = $this->read('public/assets/js/catalyst/runtime/activity-manager.js');
        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $activeSource = $this->readDirectory('public/assets/js/catalyst', ['js']);

        Assert::contains("loadRuntimeModule('core.activity'", $runtime);
        Assert::contains('new ActivityManager(', $runtime);
        Assert::contains('activity.ready()', $runtime);
        Assert::same(1, substr_count($activeSource, 'class ActivityManager'));
        Assert::contains('begin(options = {})', $activity);
        Assert::contains('finish(token)', $activity);
        Assert::contains('this.tokens = new Map()', $activity);
        Assert::contains('[data-catalyst-activity-release]', $activity);
        Assert::contains('this.reset()', $activity);
        Assert::contains("'is_development' => defined('IS_DEVELOPMENT')", $scope);
    }

    public function testActivityManagerCoordinatesInternalNavigationAndNativeSubmits(): void
    {
        $activity = $this->read('public/assets/js/catalyst/runtime/activity-manager.js');

        Assert::contains("addEventListener('click'", $activity);
        Assert::contains("addEventListener('submit'", $activity);
        Assert::contains("event.defaultPrevented", $activity);
        Assert::contains('event.stopImmediatePropagation()', $activity);
        Assert::contains('this.tokens.size > 0', $activity);
        Assert::contains("target !== '' && target !== '_self'", $activity);
        Assert::contains('download', $activity);
        Assert::contains('event.metaKey', $activity);
        Assert::contains('event.ctrlKey', $activity);
    }

    public function testHttpClientGuaranteesForegroundStartAndFinishEvents(): void
    {
        $http = $this->read('public/assets/js/catalyst/core/http.js');

        Assert::contains("'catalyst:http:start'", $http);
        Assert::contains("'catalyst:http:finish'", $http);
        Assert::contains('finally', $http);
        Assert::contains('options.background !== true', $http);
        Assert::contains('delete prepared.background', $http);
    }

    public function testForegroundNotificationsWaitForActivityIdleAndRenderAboveOverlay(): void
    {
        $http = $this->read('public/assets/js/catalyst/core/http.js');
        $notifications = $this->read('public/assets/css/catalyst/notifications.css');

        Assert::contains('processNotificationsWhenActivityIdle', $http);
        Assert::contains("'catalyst:activity:idle'", $http);
        Assert::contains('requestAnimationFrame', $http);
        Assert::contains('z-index: 2050;', $notifications);
    }

    public function testAutomaticRuntimeTransportsAreExplicitlyBackground(): void
    {
        foreach ([
            'public/assets/js/catalyst/presence/record-presence.js',
            'public/assets/js/catalyst/shell/status-bar.js',
            'public/assets/js/catalyst/notifications/flash.js',
        ] as $path) {
            Assert::contains(
                'background: true',
                $this->read($path),
                "{$path} must not block the UI for automatic runtime transport."
            );
        }
    }

    public function testTestFeaturesExposesFocusedActivityDiagnosticsWithoutAlternateWaitModal(): void
    {
        $routes = $this->read('Repository/Framework/DevTools/routes.php');
        $controller = $this->read('Repository/Framework/DevTools/Controllers/ToasterTestController.php');
        $page = $this->read('Repository/Framework/DevTools/Views/pages/test-features.phtml');
        $partial = $this->read('Repository/Framework/DevTools/Views/partials/_tf-activity.phtml');
        $script = $this->read('Repository/Framework/DevTools/front/script.js');

        Assert::contains('/test-features/api/js-enhancements/partial-refresh', $routes);
        Assert::contains("activity_probe", $controller);
        Assert::contains("\$probe === 'success'", $controller);
        Assert::contains("\$probe === 'error'", $controller);
        Assert::contains("__('devtools.activity_runtime.error'), 422", $controller);
        Assert::false(str_contains($controller, "__('devtools.activity_runtime.error'), 500"));
        Assert::contains('../partials/_tf-activity', $page);
        Assert::contains('data-devtools-action="activity-foreground"', $partial);
        Assert::contains('data-devtools-action="activity-background"', $partial);
        Assert::contains('data-devtools-action="activity-concurrent"', $partial);
        Assert::contains('data-devtools-action="activity-error"', $partial);
        Assert::contains('data-activity-native-submit', $partial);
        Assert::contains('method="GET" action="/uml"', $partial);
        Assert::contains("case 'activity-foreground':", $script);
        Assert::contains('background: true', $script);
        Assert::contains('Promise.all(', $script);
        Assert::false(str_contains($script, 'showWaitModal'));
        Assert::false(str_contains($script, 'closeWaitModal'));
    }

    private function readDirectory(string $path, array $extensions): string
    {
        $source = '';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->path($path), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo
                || !$file->isFile()
                || !in_array($file->getExtension(), $extensions, true)
            ) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if (is_string($contents)) {
                $source .= "\n" . $contents;
            }
        }

        return $source;
    }

    private function read(string $path): string
    {
        $contents = file_get_contents($this->path($path));
        if (!is_string($contents)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $contents;
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
