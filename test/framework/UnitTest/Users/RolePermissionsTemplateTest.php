<?php

declare(strict_types=1);

namespace CatalystTest\Users;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class RolePermissionsTemplateTest extends TestCase
{
    public function testPermissionOptionCardDoesNotUseFormCheckAsTheBorderedWrapper(): void
    {
        $template = file_get_contents(dirname(__DIR__, 4) . '/Repository/Framework/Users/Views/pages/permissions.phtml');

        if (!is_string($template)) {
            throw new \RuntimeException('Unable to read role permissions template.');
        }

        Assert::false(
            (bool) preg_match('/class="[^"]*\bform-check\b[^"]*\bborder\b/s', $template),
            'Permission option cards must not combine Bootstrap form-check offset with the bordered wrapper.'
        );
        Assert::contains('data-role-permission-option', $template);
    }
}
