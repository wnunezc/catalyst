<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class UiRuntimeArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testBodyScriptsLoadsTheCanonicalRuntimeEntry(): void
    {
        $template = $this->read('boot-core/template/_body-scripts.phtml');
        $scope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains(
            'type="module" src="{{ ui_runtime_asset_url }}"',
            $template,
            'The shared body scripts template must load the canonical UI runtime.'
        );
        Assert::contains(
            "AssetUrl::versionedTree(\n                '/assets/js/catalyst/runtime/ui-runtime.js',\n                '/assets/js/catalyst'\n            )",
            $scope,
            'The canonical runtime URL must be cache-busted centrally.'
        );
    }

    public function testRuntimeStartsOnTheDocumentBodyWithoutProfiles(): void
    {
        $source = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');

        Assert::contains('document.body', $source, 'The runtime must use document.body as its initial root.');
        Assert::contains('bootCatalystUiRuntime', $source, 'The runtime entry must bootstrap itself.');
        Assert::false(str_contains($source, 'profile'), 'The runtime must not require surface profiles.');
        Assert::false(str_contains($source, 'data-catalyst-ui-root'), 'The runtime must not require a root marker.');
    }

    public function testComponentAdaptersUseDomCapabilitySelectors(): void
    {
        $source = $this->read('public/assets/js/catalyst/runtime/component-registry.js');
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');
        $charts = $this->read('public/assets/js/catalyst/inspinia/charts.js');

        Assert::contains('selector', $source, 'Component adapters must declare DOM capability selectors.');
        Assert::contains('matchesCapability', $source, 'The registry must evaluate DOM capabilities.');
        Assert::contains("'.apex-charts", $runtime, 'Chart initialization must be guarded by chart DOM.');
        Assert::contains('[id^="chart-"]', $runtime, 'Generic Inspinia EChart identifiers must activate chart initialization.');
        Assert::contains(
            '[data-catalyst-inspinia-document^="charts-"]',
            $runtime,
            'Demo UI chart documents must activate chart initialization even when their IDs are map-specific.'
        );
        Assert::contains("docFile.startsWith('charts-echart-')", $charts, 'The chart adapter must recognize EChart documents.');
        Assert::contains("'[data-simplebar]'", $runtime, 'SimpleBar initialization must be guarded by its DOM contract.');
        Assert::contains('loadRuntimeModule(', $runtime, 'Capability adapters must load their modules lazily.');
    }

    public function testSimpleBarMountsTheScanRootAndItsDescendants(): void
    {
        $source = $this->read('public/assets/js/catalyst/shell/simplebar.js');

        Assert::contains("root.matches?.('[data-simplebar]') === true", $source);
        Assert::contains("root.querySelectorAll('[data-simplebar]')", $source);
        Assert::contains('simpleBarElements(root).forEach', $source);
    }

    public function testBootstrapModalRescansDoNotClearAnOpenModalDisplayState(): void
    {
        $components = $this->read('public/assets/js/catalyst/bootstrap/components.js');

        Assert::false(
            str_contains($components, "element.style.removeProperty('display')"),
            'Bootstrap modal rescans must not clear the display state owned by an open Bootstrap instance.'
        );
    }

    public function testDemoUiAppearanceBootstrapIsExternalAndDataDriven(): void
    {
        $layout = $this->read('boot-core/template/_head-assets.phtml');
        $scope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains(
            'type="application/json" id="catalyst-appearance-config"',
            $layout,
            'Demo UI appearance configuration must use a JSON transport.'
        );
        Assert::contains(
            'src="{{ appearance_bootstrap_asset_url }}"',
            $layout,
            'Demo UI must use the shared external appearance bootstrap.'
        );
        Assert::contains(
            "AssetUrl::versioned('/assets/js/catalyst/appearance-bootstrap.js')",
            $scope,
            'The shared appearance bootstrap URL must be cache-busted centrally.'
        );
        Assert::false(
            str_contains($layout, '(function () {'),
            'Demo UI must not keep an executable appearance bootstrap inline.'
        );
    }

    public function testDemoUiUsesOnlyTheCanonicalBodyRuntimeStack(): void
    {
        $bodyScripts = $this->read('boot-core/template/_body-scripts.phtml');
        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');

        Assert::contains(
            'src="{{ ui_runtime_asset_url }}"',
            $bodyScripts,
            'The common body template must load the canonical runtime.'
        );
        Assert::contains(
            "AssetUrl::versionedTree(\n                '/assets/js/catalyst/runtime/ui-runtime.js',\n                '/assets/js/catalyst'\n            )",
            $scope,
            'The common body template runtime variable must resolve to the canonical entry.'
        );
        Assert::false(
            str_contains($runtime, 'shell-dropdowns'),
            'The canonical runtime must not load a competing dropdown governor.'
        );
    }

    public function testSurfaceExtensionsUseTheCentralRegistrationQueue(): void
    {
        $queue = $this->read('public/assets/js/catalyst/runtime/registration-queue.js');
        $demoScript = $this->read('Repository/Framework/DemoUi/front/script.js');

        Assert::contains('consumeUiRegistrations', $queue, 'The runtime must be able to consume registrations made before boot.');
        Assert::contains('registerUiEvent', $demoScript, 'Demo UI must declare its modal extension through the central queue.');
        Assert::false(
            str_contains($demoScript, 'DOMContentLoaded'),
            'Surface scripts must not start themselves on DOMContentLoaded.'
        );
        Assert::false(str_contains($demoScript, 'window.Catalyst'), 'Surface registration must not race the Catalyst facade.');
    }

    public function testRegistrationQueueSupportsPendingAndLateRegistrations(): void
    {
        $queue = $this->read('public/assets/js/catalyst/runtime/registration-queue.js');
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');

        Assert::contains(
            'componentAdapters.forEach(onComponent)',
            $queue,
            'Registrations made before runtime boot must be consumed.'
        );
        Assert::contains(
            'componentListeners.forEach',
            $queue,
            'Registrations made after runtime boot must be delivered immediately.'
        );
        Assert::contains(
            'consumeUiRegistrations({',
            $runtime,
            'The canonical runtime must consume the shared registration queue.'
        );
    }

    public function testLateComponentsRemainSerializedAndCapabilityDriven(): void
    {
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');

        Assert::contains(
            'const registeredAdapter = this.components.get(componentAdapter.name);',
            $runtime,
            'Late registration must use the normalized component definition.'
        );
        Assert::contains(
            'this.components.matchesCapability(registeredAdapter, this.root)',
            $runtime,
            'Late registration must respect the declared DOM capability.'
        );
        Assert::contains(
            'void this.enqueue(async () => {',
            $runtime,
            'Late component mounts must remain in the runtime serial queue.'
        );
        Assert::false(
            str_contains($runtime, 'Promise.resolve(componentAdapter.mount'),
            'Late components must not mount outside the runtime queue.'
        );
    }

    public function testLocalDependencyFailuresAreObservableAndRetryable(): void
    {
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');

        Assert::contains(
            'Unable to load local runtime dependency',
            $runtime,
            'Missing local dependencies must produce a clear runtime error.'
        );
        Assert::contains(
            'modulePromises.delete(name)',
            $runtime,
            'A failed dependency load must be removable so a later scan can retry it.'
        );
    }

    public function testSurfaceScriptsDoNotStartAutonomously(): void
    {
        $scripts = glob($this->root . '/Repository/*/*/front/script.js') ?: [];
        $scripts = array_merge(
            $scripts,
            glob($this->root . '/Repository/*/*/*/front/script.js') ?: []
        );

        foreach ($scripts as $script) {
            $source = file_get_contents($script);
            if (!is_string($source)) {
                throw new \RuntimeException("Unable to read {$script}.");
            }

            Assert::false(
                str_contains($source, 'DOMContentLoaded'),
                "{$script} must not start itself on DOMContentLoaded."
            );
        }
    }

    private function read(string $path): string
    {
        $source = file_get_contents($this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
