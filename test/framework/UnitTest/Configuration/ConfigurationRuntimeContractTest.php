<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ConfigurationRuntimeContractTest extends TestCase
{
    public function testConfigurationRuntimeSupportsDirectedRescansWithoutSecondaryGovernors(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents(
            $root . '/Repository/Framework/Configuration/front/script.js'
        );

        Assert::contains("name: 'configuration.module'", $script);
        Assert::contains('mount: bootstrapConfigurationModule', $script);
        Assert::contains('function bootstrapConfigurationModule(root = document.body)', $script);
        Assert::contains('configurationSessionBound', $script);
        Assert::contains('configurationDkimBound', $script);
        Assert::contains('configurationEventsBound', $script);
        Assert::false(str_contains($script, 'settingsModuleBooted'));
        Assert::false(str_contains($script, 'DOMContentLoaded'));
        Assert::false(str_contains($script, 'new FormHandler'));
        Assert::false(str_contains($script, 'MutationObserver'));
    }

    public function testConfigurationControllerHasNoUnroutedLegacyRedirect(): void
    {
        $root = dirname(__DIR__, 4);
        $controller = (string) file_get_contents(
            $root . '/Repository/Framework/Configuration/Controllers/ConfigController.php'
        );

        Assert::false(str_contains($controller, 'redirectCanonical'));
        Assert::false(str_contains($controller, 'RedirectResponse'));
    }
}
