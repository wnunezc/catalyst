<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;
use RuntimeException;

final class FeatureFlagsMigrationContractTest extends TestCase
{
    public function testFeatureFlagsSurfaceBelongsOnlyToConfiguration(): void
    {
        $root = dirname(__DIR__, 4);
        $configuration = file_get_contents($root . '/Repository/Framework/Configuration/routes.php');

        Assert::true(str_contains((string) $configuration, '/configuration/feature-flags'));
        Assert::false(is_dir($root . '/Repository/Framework/Operations'));
        Assert::true(is_file($root . '/Repository/Framework/Configuration/Controllers/FeatureFlagsController.php'));
        Assert::false(is_file($root . '/Repository/Framework/Operations/Controllers/FeatureFlagsController.php'));
    }

    public function testFeatureFlagKeysAndScopesAreRejectedBeforePersistence(): void
    {
        Assert::true(FeatureFlagManager::isValidKey('module.framework.audit'));
        Assert::false(FeatureFlagManager::isValidKey('../invalid'));
        Assert::false(FeatureFlagManager::isValidKey('Uppercase.Flag'));

        try {
            FeatureFlagManager::getInstance()->persistCatalog([
                'valid.flag' => ['enabled' => true, 'scope' => 'invalid'],
            ]);
        } catch (RuntimeException) {
            return;
        }

        Assert::true(false, 'Expected an invalid Feature Flag scope to be rejected.');
    }
}
