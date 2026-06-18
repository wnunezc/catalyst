<?php

declare(strict_types=1);

namespace CatalystTest\Security;

use Catalyst\Framework\Auth\AuthInputGuard;
use Catalyst\Framework\Auth\MfaManager;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class SecurityHardeningContractTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testPasswordPolicyUsesSecureDefaultsAndRejectsCommonPasswords(): void
    {
        $policy = AuthInputGuard::passwordPolicy();

        Assert::same(12, $policy['minLength']);
        Assert::true($policy['requireUppercase']);
        Assert::true($policy['requireLowercase']);
        Assert::true($policy['requireNumber']);
        Assert::true($policy['requireSymbol']);
        Assert::true($policy['blockCommon']);
        Assert::true(AuthInputGuard::passwordPolicyErrors('password123') !== []);
        Assert::same([], AuthInputGuard::passwordPolicyErrors('Strong-Password-2026!'));
    }

    public function testTotpVerificationExposesTheAcceptedStepForReplayProtection(): void
    {
        $manager = MfaManager::getInstance();
        $secret = $manager->generateSecret();
        $step = (int) floor(time() / 30);
        $method = new \ReflectionMethod($manager, 'computeTotp');
        $decode = new \ReflectionMethod($manager, 'base32Decode');
        $code = $method->invoke($manager, $decode->invoke($manager, $secret), $step);

        Assert::same($step, $manager->verifiedTimeStep($secret, $code, 0));
        Assert::same(null, $manager->verifiedTimeStep($secret, '00000A', 0));

        $provider = $this->read('app/Framework/Auth/UserProvider.php');
        $controller = $this->read('Repository/Framework/Auth/Controllers/MfaController.php');
        $migration = $this->read('boot-core/database/migrations/20260615010000_add_mfa_totp_replay_guard.php');

        Assert::contains('consumeMfaTotpStep', $provider);
        Assert::contains('consumeMfaTotpStep($userId, $verifiedTimeStep)', $controller);
        Assert::contains('mfa_last_totp_step', $migration);
    }

    public function testCspNonceGenerationHasNoInsecureFallback(): void
    {
        $source = $this->read('app/Helpers/Security/CspNonce.php');

        Assert::contains('random_bytes(16)', $source);
        Assert::contains('throw new RuntimeException(', $source);
        Assert::false(str_contains($source, 'uniqid('));
        Assert::false(str_contains($source, 'mt_rand('));
    }

    public function testMediaUploadsBlockActiveContentByDefault(): void
    {
        $request = $this->read('Repository/Framework/Workspaces/Media/Requests/MediaItemRequest.php');
        $config = $this->read('boot-core/config/templates/security.json');
        $english = $this->read('Repository/Framework/Workspaces/Media/lang/en/media.json');
        $spanish = $this->read('Repository/Framework/Workspaces/Media/lang/es/media.json');

        Assert::contains("DEFAULT_BLOCKED_EXTENSIONS = ['php', 'phtml', 'phar', 'cgi', 'pl', 'htm', 'html', 'svg']", $request);
        Assert::contains("DEFAULT_BLOCKED_MIME_TYPES = ['application/x-php', 'text/html', 'image/svg+xml']", $request);
        Assert::contains('security.security.uploads', $request);
        Assert::contains("__('media.library.validation.blocked_extension'", $request);
        Assert::contains("__('media.library.validation.blocked_mime_type'", $request);
        Assert::contains('"allow_svg": false', $config);
        Assert::contains('"blocked_extension"', $english);
        Assert::contains('"blocked_mime_type"', $english);
        Assert::contains('"blocked_extension"', $spanish);
        Assert::contains('"blocked_mime_type"', $spanish);
    }

    public function testCsrfFailureUsesFormStateExpiredContract(): void
    {
        $middleware = $this->read('app/Framework/Middleware/CsrfMiddleware.php');

        Assert::contains("'code' => 'form_state_expired'", $middleware);
        Assert::contains("'refresh_required' => true", $middleware);
        Assert::contains("'new_token' =>", $middleware);
        Assert::contains('This form is no longer valid due to inactivity.', $middleware);
        Assert::false(str_contains($middleware, 'Your session expired.'));
    }

    private function read(string $relative): string
    {
        $source = file_get_contents(
            $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)
        );

        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$relative}.");
        }

        return $source;
    }
}
