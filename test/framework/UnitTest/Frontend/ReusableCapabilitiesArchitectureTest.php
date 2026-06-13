<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ReusableCapabilitiesArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testRuntimeRegistersNeutralReusableCapabilities(): void
    {
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');

        Assert::contains("'core.declarative-actions'", $runtime);
        Assert::contains("'forms.password'", $runtime);
        Assert::contains("'notifications.flash'", $runtime);
        Assert::contains('../core/declarative-actions.js', $runtime);
        Assert::contains('../forms/password.js', $runtime);
        Assert::contains('../notifications/flash.js', $runtime);
        Assert::false(str_contains($runtime, 'confirmDeclaredAction'));
    }

    public function testDeclarativeActionsAreGenericAndRuntimeOwned(): void
    {
        $actions = $this->read('public/assets/js/catalyst/core/declarative-actions.js');

        Assert::contains('[data-confirm]', $actions);
        Assert::contains('[data-history-back]', $actions);
        Assert::contains('[data-catalyst-href]', $actions);
        Assert::contains('export function initDeclarativeActions', $actions);
        Assert::false(str_contains($actions, 'DOMContentLoaded'));
        Assert::false(str_contains($actions, 'DevTools'));
        Assert::false(str_contains($actions, 'Admin'));
    }

    public function testPasswordCapabilitySupportsInitialAndDynamicRoots(): void
    {
        $password = $this->read('public/assets/js/catalyst/forms/password.js');
        $catalyst = $this->read('public/assets/js/catalyst/catalyst.js');

        Assert::contains('data-password-toggle', $password);
        Assert::contains('strengthInputs', $password);
        Assert::contains('scanRoot', $password);
        Assert::contains('eventRoot', $password);
        Assert::false(str_contains($password, 'DOMContentLoaded'));
        Assert::false(str_contains($catalyst, 'this.passwords.init()'));
    }

    public function testFlashUsesCentralSsrStateAndSharedHttpClient(): void
    {
        $flash = $this->read('public/assets/js/catalyst/notifications/flash.js');
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');
        $scope = $this->read('app/Framework/View/DocumentScope.php');
        $head = $this->read('boot-core/template/_head-assets.phtml');

        Assert::contains('export function initFlashMessages', $flash);
        Assert::contains('data-flash-dismiss', $flash);
        Assert::contains("http.json('/runtime/flash/dismiss'", $flash);
        Assert::contains('paragraph.textContent = text', $flash);
        Assert::contains('ssrState?.flash', $runtime);
        Assert::contains("AssetUrl::versioned('/assets/css/catalyst/notifications.css')", $scope);
        Assert::contains('href="{{ notifications_asset_url }}"', $head);
        Assert::false(str_contains($flash, 'DOMContentLoaded'));
    }

    public function testBootstrapPrimitivesDoNotImplementBootstrapFallbacks(): void
    {
        $primitives = $this->read('public/assets/js/catalyst/bootstrap/primitives.js');

        Assert::contains('initLiveAlertDemo', $primitives);
        Assert::false(str_contains($primitives, 'removeDismissTarget'));
        Assert::false(str_contains($primitives, 'hideBootstrapTarget'));
        Assert::false(str_contains($primitives, 'toggleButtonState'));
        Assert::false(str_contains($primitives, "getAttribute('data-bs-dismiss')"));
        Assert::false(str_contains($primitives, 'window.bootstrap'));
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
