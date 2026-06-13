<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use Catalyst\Framework\Http\Request;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;
use Catalyst\Repository\Configuration\Requests\CorsConfigRequest;
use Catalyst\Repository\Configuration\Requests\DkimGenerateRequest;
use Catalyst\Repository\Configuration\Requests\FtpConfigRequest;
use Catalyst\Repository\Configuration\Requests\FeatureFlagDefaultRequest;
use Catalyst\Repository\Configuration\Requests\FeatureFlagOverrideRequest;
use Catalyst\Repository\Configuration\Requests\SetupPrivilegedAccountRequest;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ConfigurationRequestsTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/constant/sys-constant.php';
        if (!defined('IS_DEVELOPMENT')) {
            define('IS_DEVELOPMENT', false);
        }
    }

    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_SERVER = [];
        Request::resetInstance();
    }

    public function testFtpRequestNormalizesPayloadAndPreservesStoredPassword(): void
    {
        $payload = $this->request([
            'ftp_protocol' => 'SFTP',
            'ftp_host' => 'files.example.com',
            'ftp_port' => '22',
            'ftp_username' => ' deploy ',
            'ftp_password' => '',
            'ftp_root' => '\\releases\\current\\',
            'ftp_timeout' => '15',
            'ftp_passive' => '1',
        ]);

        $resolved = (new FtpConfigRequest($payload))->resolved(['ftp_password' => 'stored-secret']);

        Assert::same('sftp', $resolved['ftp_protocol']);
        Assert::same('/releases/current', $resolved['ftp_root']);
        Assert::same('stored-secret', $resolved['ftp_password']);
        Assert::same(22, $resolved['ftp_port']);
    }

    public function testCorsRequestRejectsWildcardCredentialsAndMalformedTokens(): void
    {
        $payload = $this->request([
            'cors_allowed_origins' => '*,javascript:alert(1)',
            'cors_allowed_methods' => 'GET,TRACE',
            'cors_allowed_headers' => 'Content-Type,Bad Header',
            'cors_allow_credentials' => '1',
            'cors_max_age' => '86400',
        ]);

        $this->expectValidationFailure(static fn() => (new CorsConfigRequest($payload))->validated());
    }

    public function testCorsRequestProducesNormalizedPolicy(): void
    {
        $payload = $this->request([
            'cors_enabled' => '1',
            'cors_allowed_origins' => 'https://app.example.com, http://localhost:8080',
            'cors_allowed_methods' => 'get,post,options',
            'cors_allowed_headers' => 'Content-Type,Authorization',
            'cors_exposed_headers' => 'X-Request-Id',
            'cors_allow_credentials' => '1',
            'cors_max_age' => '600',
        ]);

        $resolved = (new CorsConfigRequest($payload))->validated();

        Assert::same(['GET', 'POST', 'OPTIONS'], $resolved['allowed_methods']);
        Assert::same(600, (int) $resolved['cors_max_age']);
        Assert::true($resolved['allow_credentials']);
    }

    public function testDkimRequestRejectsPathLikeIdentifiers(): void
    {
        $payload = $this->request([
            'dkim_domain' => '../example.com',
            'dkim_selector' => '../selector',
            'dkim_connection' => '..\\mail1',
        ]);

        $this->expectValidationFailure(static fn() => (new DkimGenerateRequest($payload))->validated());
    }

    public function testSetupPrivilegedAccountRequestNormalizesEmailAndRejectsPasswordMismatch(): void
    {
        $valid = $this->request([
            'account_name' => ' Initial Privileged Account ',
            'account_email' => ' PRIVILEGED@EXAMPLE.COM ',
            'account_password' => 'correct-password',
            'account_password_confirm' => 'correct-password',
        ]);
        $resolved = (new SetupPrivilegedAccountRequest($valid))->validated();

        Assert::same('Initial Privileged Account', $resolved['account_name']);
        Assert::same('privileged@example.com', $resolved['account_email']);

        $invalid = $this->request([
            'account_name' => 'Initial Privileged Account',
            'account_email' => 'privileged@example.com',
            'account_password' => 'correct-password',
            'account_password_confirm' => 'different-password',
        ]);
        $this->expectValidationFailure(static fn() => (new SetupPrivilegedAccountRequest($invalid))->validated());
    }

    public function testFeatureFlagRequestsNormalizeCheckboxAndRejectInvalidSubjects(): void
    {
        $enabled = new FeatureFlagDefaultRequest($this->request(['enabled' => 'on']));
        Assert::true($enabled->enabled());

        $payload = [
            'flag_key' => '../invalid',
            'subject_type' => 'tenant',
            'subject_key' => '../admin',
            'enabled' => '1',
            'note' => 'invalid',
        ];
        $request = new FeatureFlagOverrideRequest($this->request($payload));
        $validator = new Validator($payload, $request->rules());

        Assert::true($validator->fails());
    }

    private function request(array $post): Request
    {
        $_GET = [];
        $_POST = $post;
        $_FILES = [];
        $_SERVER = ['REQUEST_METHOD' => 'POST'];
        Request::resetInstance();

        return Request::getInstance();
    }

    private function expectValidationFailure(callable $callback): void
    {
        try {
            $callback();
        } catch (ValidationException) {
            return;
        }

        Assert::true(false, 'Expected payload validation to fail.');
    }
}
