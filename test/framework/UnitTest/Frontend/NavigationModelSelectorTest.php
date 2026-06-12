<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use Catalyst\Framework\Navigation\NavigationModelSelector;
use Catalyst\Helpers\I18n\Translator;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class NavigationModelSelectorTest extends TestCase
{
    public function setUp(): void
    {
        $root = dirname(__DIR__, 4);
        require_once $root . '/boot-core/constant/sys-constant.php';
        Translator::getInstance()->init('en', $root . '/boot-core/lang');
        Translator::getInstance()->clearCache();
    }

    public function testExposesOnlyTheThreeApprovedModels(): void
    {
        Assert::same([
            'demo-ui',
            'framework-admin',
            'application',
        ], NavigationModelSelector::getInstance()->ids());
    }

    public function testRejectsUnknownModelsWithoutFallback(): void
    {
        Assert::same([], NavigationModelSelector::getInstance()->select('unknown', [
            'current_path' => '/',
        ]));
    }

    public function testDemoUiModelBuildsOnlyApprovedFrameworkLinksAndComponentCatalog(): void
    {
        $tree = NavigationModelSelector::getInstance()->select('demo-ui', [
            'current_path' => '/demo-ui/charts/apex/area',
            'selected_file' => 'charts-apex-area.html',
            'selected_section' => 'charts',
            'sections' => [
                'base-ui' => [
                    ['file' => 'ui-alerts.html', 'label' => 'Alerts'],
                ],
                'forms' => [
                    ['file' => 'form-elements.html', 'label' => 'Basic Elements'],
                ],
            ],
            'chart_families' => [
                'apex' => ['label' => 'Apex Charts', 'slugs' => ['apex-area']],
            ],
            'chart_pages' => [
                'apex-area' => [
                    'file' => 'charts-apex-area.html',
                    'label' => 'Area',
                    'route' => '/demo-ui/charts/apex/area',
                ],
            ],
            'table_families' => [
                'datatables' => ['label' => 'DataTables', 'badge' => 'DT', 'slugs' => ['datatables-basic']],
            ],
            'table_pages' => [
                'static' => [
                    'file' => 'tables-static.html',
                    'label' => 'Static',
                    'route' => '/demo-ui/tables/static',
                ],
                'datatables-basic' => [
                    'file' => 'tables-datatables-basic.html',
                    'label' => 'Basic',
                    'route' => '/demo-ui/tables/datatables/basic',
                ],
            ],
            'catalogs' => [
                [
                    'file' => 'ui-alerts.html',
                    'label' => 'Alerts',
                    'route' => '/demo-ui/alerts',
                ],
                [
                    'file' => 'form-elements.html',
                    'label' => 'Basic Elements',
                    'route' => '/demo-ui/basic-elements',
                ],
            ],
        ]);

        Assert::same([
            'Framework',
            'Configuration',
            'Operations',
            'Users',
            'Components',
            'Base UI',
            'Charts',
            'Forms',
            'Tables',
        ], array_column($tree, 'label'));
        Assert::same('/configuration/environment-setup', $tree[1]['href']);
        Assert::same('/operations/deployments', $tree[2]['href']);
        Assert::same('/users', $tree[3]['href']);
        Assert::same('Apex Charts', $tree[6]['children'][0]['label']);
        Assert::same('Area', $tree[6]['children'][0]['children'][0]['label']);
        Assert::true($tree[6]['is_active']);
        Assert::false(in_array('Workspaces', array_column($tree, 'label'), true));
        Assert::false(in_array('Devtools', array_column($tree, 'label'), true));
    }

    public function testDemoUiProviderRejectsArbitraryPrebuiltNodes(): void
    {
        Assert::same([], NavigationModelSelector::getInstance()->select('demo-ui', [
            'current_path' => '/demo-ui',
            'nodes' => [
                [
                    'kind' => 'link',
                    'label' => 'Injected',
                    'href' => '/injected',
                ],
            ],
            'sections' => [],
            'chart_families' => [],
            'chart_pages' => [],
            'table_families' => [],
            'table_pages' => [],
            'catalogs' => [],
        ]));
    }

    public function testApplicationModelUsesFrameworkAndRegisteredAppOwners(): void
    {
        $tree = NavigationModelSelector::getInstance()->select('application', [
            'current_path' => '/dashboard',
            'user' => ['id' => 1],
        ]);

        Assert::true(in_array(__('account.nav.dashboard'), array_column($tree, 'label'), true));
        Assert::true(in_array(__('account.nav.account'), array_column($tree, 'label'), true));
        Assert::true(in_array(__('account.nav.profile'), array_column($tree, 'label'), true));
        Assert::true($tree[1]['is_active']);
    }

    public function testApplicationModelRejectsControllerInjectedTrees(): void
    {
        $tree = NavigationModelSelector::getInstance()->select('application', [
            'current_path' => '/injected',
            'user' => ['id' => 1],
            'framework_nodes' => [
                ['kind' => 'link', 'label' => 'Injected Framework', 'href' => '/injected'],
            ],
            'app_nodes' => [
                ['kind' => 'link', 'label' => 'Injected App', 'href' => '/injected'],
            ],
        ]);

        Assert::false(in_array('Injected Framework', array_column($tree, 'label'), true));
        Assert::false(in_array('Injected App', array_column($tree, 'label'), true));
    }
}
