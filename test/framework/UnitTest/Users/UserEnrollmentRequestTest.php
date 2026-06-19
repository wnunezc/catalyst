<?php

declare(strict_types=1);

namespace CatalystTest\Users;

use Catalyst\Framework\Http\Request;
use Catalyst\Repository\Users\Requests\UserEnrollmentRequest;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class UserEnrollmentRequestTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/requirement-loader/error-catcher.php';
    }

    public function testAcceptsAValidEnrollmentPayload(): void
    {
        $request = new UserEnrollmentRequest($this->request([
            'name' => 'Valid User',
            'email' => 'valid@example.test',
            'role' => 'user',
            'email_verified' => '1',
        ]));
        $payload = $request->payload();

        Assert::same([], $request->errors($payload));
        Assert::same('valid@example.test', $payload['email']);
        Assert::false(isset($payload['password']));
        Assert::false(isset($payload['password_confirm']));
    }

    public function testRejectsInvalidInputAndNeverReplaysPasswords(): void
    {
        $request = new UserEnrollmentRequest($this->request([
            'name' => 'A',
            'email' => 'invalid',
            'role' => '',
            'email_verified' => '0',
        ]));
        $payload = $request->payload();
        $errors = $request->errors($payload);
        $replayable = $request->replayableInput($payload);

        Assert::true(isset($errors['name']));
        Assert::true(isset($errors['email']));
        Assert::false(isset($errors['password']));
        Assert::false(isset($errors['password_confirm']));
        Assert::true(isset($errors['role']));
        Assert::false(isset($replayable['password']));
        Assert::false(isset($replayable['password_confirm']));
    }

    public function testEnrollmentFormDoesNotExposeAdminPasswordFields(): void
    {
        $factory = file_get_contents(dirname(__DIR__, 4) . '/Repository/Framework/Users/Support/UserEnrollmentFormFactory.php');

        if (!is_string($factory)) {
            throw new \RuntimeException('Unable to read user enrollment form factory.');
        }

        Assert::false(str_contains($factory, "'password' =>"));
        Assert::false(str_contains($factory, "'password_confirm' =>"));
        Assert::contains('onboarding', $factory);
    }

    /**
     * @param array<string, string> $post
     */
    private function request(array $post): Request
    {
        $_GET = [];
        $_POST = $post;
        $_COOKIE = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        return new class extends Request {
            public function __construct()
            {
                parent::__construct();
            }
        };
    }
}
