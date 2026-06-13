<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use Catalyst\Framework\Plugin\PluginManager;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;
use RuntimeException;

final class PluginsMigrationContractTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/constant/sys-constant.php';
    }

    public function testPluginsSurfaceBelongsOnlyToConfiguration(): void
    {
        $root = dirname(__DIR__, 4);
        $configuration = file_get_contents($root . '/Repository/Framework/Configuration/routes.php');

        Assert::true(str_contains((string) $configuration, '/configuration/plugins'));
        Assert::true(is_file($root . '/Repository/Framework/Configuration/Controllers/PluginsController.php'));
        Assert::false(is_file($root . '/Repository/Framework/Operations/Controllers/PluginsController.php'));
    }

    public function testInvalidAndRequiredTransitionsDoNotMutatePluginConfig(): void
    {
        $config = dirname(__DIR__, 4) . '/boot-core/config/development/plugins.json';
        $before = hash_file('sha256', $config);
        $manager = PluginManager::getInstance();

        foreach ([
            static fn() => $manager->setEnabled('../invalid', false),
            static fn() => $manager->setEnabled('framework.core', false),
        ] as $transition) {
            try {
                $transition();
                Assert::true(false, 'Expected the plugin transition to be rejected.');
            } catch (RuntimeException) {
            }
        }

        Assert::same($before, hash_file('sha256', $config));
        Assert::true(PluginManager::isValidKey('framework.business'));
        Assert::false(PluginManager::isValidKey('../framework.business'));
    }
}
