<?php

declare(strict_types=1);

namespace CatalystTest\Controllers;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class PostActionRedirectFeedbackArchitectureTest extends TestCase
{
    private string $method;

    public function setUp(): void
    {
        $path = dirname(__DIR__, 4) . '/app/Framework/Controllers/Controller.php';
        $source = file_get_contents($path);

        if (!is_string($source)) {
            throw new \RuntimeException('Unable to read Controller.php.');
        }

        $start = strpos($source, 'private function postActionRedirect(');
        if ($start === false) {
            throw new \RuntimeException('Unable to find postActionRedirect().');
        }

        $this->method = substr($source, $start);
    }

    public function testImmediateRedirectQueuesFeedbackWithoutJsonNotification(): void
    {
        Assert::contains('$this->toast(\'success\', $message);', $this->method);
        Assert::contains('$this->flash()->error($message);', $this->method);
        Assert::contains('? $this->jsonSuccess($data, $message, $status)', $this->method);
        Assert::contains(': $this->jsonError($message, $status, $data);', $this->method);
        Assert::false(str_contains($this->method, '$delay'));
        Assert::false(str_contains($this->method, 'jsonSuccessWithToast'));
        Assert::false(str_contains($this->method, 'jsonErrorWithToast'));
    }

    public function testJsonNavigationContractHasNoDelayCapability(): void
    {
        $path = dirname(__DIR__, 4) . '/app/Framework/Http/JsonResponse.php';
        $source = file_get_contents($path);

        if (!is_string($source)) {
            throw new \RuntimeException('Unable to read JsonResponse.php.');
        }

        Assert::contains('public function withRedirect(string $url): self', $source);
        Assert::contains('public function withRefresh(): self', $source);
        Assert::false(str_contains($source, 'redirectDelay'));
        Assert::false(str_contains($source, 'refreshDelay'));
    }

    public function testFormHandlerNavigatesWithoutTimersOrFallbackDelay(): void
    {
        $path = dirname(__DIR__, 4) . '/public/assets/js/catalyst/forms/form-handler.js';
        $source = file_get_contents($path);

        if (!is_string($source)) {
            throw new \RuntimeException('Unable to read form-handler.js.');
        }

        Assert::false(str_contains($source, 'defaultDelay'));
        Assert::false(str_contains($source, 'redirectDelay'));
        Assert::false(str_contains($source, 'refreshDelay'));
        Assert::contains('window.location.href = data.redirect;', $source);
        Assert::contains('window.location.reload();', $source);
    }
}
