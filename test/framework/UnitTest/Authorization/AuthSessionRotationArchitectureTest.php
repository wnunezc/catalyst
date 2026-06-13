<?php

declare(strict_types=1);

namespace CatalystTest\Authorization;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class AuthSessionRotationArchitectureTest extends TestCase
{
    private string $source;

    public function setUp(): void
    {
        $path = dirname(__DIR__, 4) . '/app/Framework/Auth/AuthManager.php';
        $source = file_get_contents($path);

        if (!is_string($source)) {
            throw new \RuntimeException('Unable to read AuthManager.php.');
        }

        $this->source = $source;
    }

    public function testPendingMfaRotatesAfterClearingStaleStateWithoutDeletingAnonymousSession(): void
    {
        $method = $this->method('setPendingMfa');

        Assert::contains('$this->clearPendingMfa();', $method);
        Assert::contains('$this->clearMfaSetupPending();', $method);
        Assert::contains('$this->session->regenerateId(false);', $method);
        Assert::true(
            strpos($method, '$this->clearPendingMfa();') < strpos($method, '$this->session->regenerateId(false);')
        );
        Assert::true(
            strpos($method, '$this->clearMfaSetupPending();') < strpos($method, '$this->session->regenerateId(false);')
        );
        Assert::true(
            strpos($method, '$this->session->regenerateId(false);') < strpos($method, "'_mfa_pending_user_id'")
        );
        Assert::false(str_contains($method, 'regenerateId(true)'));
    }

    public function testPendingMfaSetupRotatesAfterClearingStaleStateWithoutDeletingAnonymousSession(): void
    {
        $method = $this->method('setPendingMfaSetup');

        Assert::contains('$this->clearPendingMfa();', $method);
        Assert::contains('$this->clearMfaSetupPending();', $method);
        Assert::contains('$this->session->regenerateId(false);', $method);
        Assert::true(
            strpos($method, '$this->clearPendingMfa();') < strpos($method, '$this->session->regenerateId(false);')
        );
        Assert::true(
            strpos($method, '$this->clearMfaSetupPending();') < strpos($method, '$this->session->regenerateId(false);')
        );
        Assert::true(
            strpos($method, '$this->session->regenerateId(false);') < strpos($method, "'_mfa_setup_pending_user_id'")
        );
        Assert::false(str_contains($method, 'regenerateId(true)'));
    }

    public function testAuthenticatedStateIsWrittenOnlyAfterNonDestructiveRotation(): void
    {
        $method = $this->method('createSession');

        Assert::contains('$this->clearPendingMfa();', $method);
        Assert::contains('$this->clearMfaSetupPending();', $method);
        Assert::contains('$this->session->regenerateId(false);', $method);
        Assert::true(
            strpos($method, '$this->session->regenerateId(false);') < strpos($method, "'_auth_logged_in'")
        );
        Assert::true(
            strpos($method, '$this->clearPendingMfa();') < strpos($method, '$this->session->regenerateId(false);')
        );
        Assert::true(
            strpos($method, '$this->clearMfaSetupPending();') < strpos($method, '$this->session->regenerateId(false);')
        );
        Assert::false(str_contains($method, 'regenerateId(true)'));
    }

    private function method(string $name): string
    {
        $start = strpos($this->source, "function {$name}(");
        if ($start === false) {
            throw new \RuntimeException("Unable to find {$name}().");
        }

        $next = strpos($this->source, "\n    public function ", $start + 1);
        if ($next === false) {
            $next = strpos($this->source, "\n    private function ", $start + 1);
        }

        return substr($this->source, $start, $next === false ? null : $next - $start);
    }
}
