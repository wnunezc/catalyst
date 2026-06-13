<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Repository\Operations\Support\OperationsAccessContract;
use Catalyst\Repository\Workspaces\Support\WorkspacesAccessContract;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class CanonicalOwnerBootstrapTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/constant/sys-constant.php';
        ModuleRegistry::getInstance()->flushCache();
    }

    public function testCanonicalOwnersAreDiscoveredDuringIncrementalSurfaceMigration(): void
    {
        $workspaces = ModuleRegistry::getInstance()->findByKey('framework.workspaces');
        $operations = ModuleRegistry::getInstance()->findByKey('framework.operations');

        Assert::true(is_array($workspaces));
        Assert::true(is_array($operations));
        Assert::true(is_array($workspaces['routes']['web'] ?? null));
        Assert::true(is_array($workspaces['routes']['api'] ?? null));
        Assert::true(is_array($operations['routes']['web'] ?? null));
        Assert::true(is_array($operations['routes']['api'] ?? null));
    }

    public function testAccessContractsExposeOnlyCanonicalCapabilities(): void
    {
        Assert::same(
            [
                'manage-workspaces-catalogs',
                'manage-workspaces-module-designer',
                'manage-workspaces-media-fields',
                'manage-workspaces-media-library',
                'manage-workspaces-document-templates',
                'manage-workspaces-localization',
            ],
            WorkspacesAccessContract::permissions()
        );
        Assert::same(
            [
                'manage-operations-deployments',
                'manage-operations-tenancy',
                'manage-operations-audit-log',
                'manage-operations-api-management',
                'manage-operations-automation-rules',
            ],
            OperationsAccessContract::permissions()
        );
    }
}
