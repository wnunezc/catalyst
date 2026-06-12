<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class SsrDocumentContractTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testDocumentScopeOwnsCsrfAndInitialNotificationState(): void
    {
        $scope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains('CsrfProtection::getInstance()->getMetaTag()', $scope);
        Assert::contains('ToastQueue::getInstance()->all()', $scope);
        Assert::contains('FlashMessage::getInstance()', $scope);
        Assert::contains("'initial_state_json'", $scope);
        Assert::contains("'has_initial_state'", $scope);
    }

    public function testCanonicalDocumentEmitsOnlyDedicatedInitialTransports(): void
    {
        $head = $this->read('boot-core/template/_head-assets.phtml');
        $body = $this->read('boot-core/template/_body-scripts.phtml');

        Assert::contains('{{{ csrf_meta_tag }}}', $head);
        Assert::contains('id="catalyst-ssr-state"', $body);
        Assert::contains('type="application/json"', $body);
        Assert::contains('{{{ initial_state_json }}}', $body);
        Assert::false(str_contains($head . $body, 'catalyst-runtime-config'));

        $statePosition = strpos($body, 'id="catalyst-ssr-state"');
        $runtimePosition = strpos($body, 'src="{{ ui_runtime_asset_url }}"');
        Assert::true(
            is_int($statePosition) && is_int($runtimePosition) && $statePosition < $runtimePosition,
            'Initial SSR state must be available before the canonical runtime loads.'
        );
    }

    public function testRuntimeDoesNotDependOnGeneralConfigurationTransport(): void
    {
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');

        Assert::contains("readJsonTransport('catalyst-ssr-state')", $runtime);
        Assert::contains('flushPendingToasts(ssrState.toasts)', $runtime);
        Assert::contains('Catalyst.init();', $runtime);
        Assert::false(str_contains($runtime, 'catalyst-runtime-config'));
        Assert::false(str_contains($runtime, 'runtimeConfig.catalyst'));
    }

    public function testAjaxTransportRemainsAnExplicitResponseContract(): void
    {
        $response = $this->read('app/Framework/Http/JsonResponse.php');
        $http = $this->read('public/assets/js/catalyst/core/http.js');

        Assert::contains("\$data['notifications']", $response);
        Assert::contains("typeof data.new_token === 'string'", $http);
        Assert::contains('this.notificationHandler.processResponse(data)', $http);
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
