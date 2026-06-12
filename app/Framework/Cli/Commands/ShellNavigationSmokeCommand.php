<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Navigation\NavigationRegistry;
use Catalyst\Framework\Navigation\ShellNavigationPresenter;

/**
 * shell-navigation:smoke CLI command.
 *
 * Responsibility: Verifies that module-declared shell navigation reaches the sidebar view model.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ShellNavigationSmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'shell-navigation:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Verify shell sidebar projection for module-declared navigation';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $definitions = (array)(NavigationRegistry::getInstance()->allDefinitions()['shell'] ?? []);
        $sidebar = ShellNavigationPresenter::fromDefinitions($definitions, '/users/organization-hierarchy');
        $expectedHrefs = $this->shellHrefs($definitions);
        $actualHrefs = $this->sidebarHrefs($sidebar);
        $missingHrefs = array_values(array_diff($expectedHrefs, $actualHrefs));
        $missingCanonicalHrefs = array_values(array_diff(ShellNavigationPresenter::canonicalHrefs(), $actualHrefs));
        $organizationItem = $this->findSidebarItem($sidebar, '/users/organization-hierarchy');
        $usersGroup = $this->findSidebarGroup($sidebar, 'Users');
        $configurationGroup = $this->findSidebarGroup($sidebar, 'Configuration');
        $titles = $this->sidebarTitles($sidebar);
        $groupLabels = $this->sidebarGroupLabels($sidebar);

        $checks = [
            'all_shell_hrefs_projected' => $missingHrefs === [],
            'canonical_hrefs_present' => $missingCanonicalHrefs === [],
            'canonical_titles_once' => $titles === ['Framework Configuration', 'Framework Operations'],
            'canonical_groups_present' => $groupLabels === ['Configuration', 'Workspaces', 'Operations', 'Users', 'Devtools'],
            'configuration_surfaces_preserved' => $this->groupContainsLabelsInOrder($configurationGroup, [
                'Environment Setup',
                'Application Health',
                'Platform Appearance',
                'Feature Flags',
                'Plugins Management',
            ]),
            'workspaces_surfaces_preserved' => $this->groupContainsLabelsInOrder($this->findSidebarGroup($sidebar, 'Workspaces'), [
                'Catalogs',
                'Module Designer',
                'Media and Documents Fields',
                'Media and Documents Library',
                'Document Template',
                'Locale Tools',
            ]),
            'operations_surfaces_preserved' => $this->groupContainsLabelsInOrder($this->findSidebarGroup($sidebar, 'Operations'), [
                'Deployments',
                'Tenancy',
                'Audit Log',
                'API Platform',
                'Automation Rules',
            ]),
            'disconnected_debt_marked' => $this->disconnectedCanonicalDebtMarked($sidebar),
            'users_surfaces_preserved' => $this->groupContainsLabelsInOrder($usersGroup, [
                'User Management',
                'User Role',
                'User Permissions',
                'User Enroll',
                'Organization Hierarchy',
                'Account Recovery',
            ]),
            'devtools_surfaces_preserved' => $this->groupContainsLabelsInOrder($this->findSidebarGroup($sidebar, 'Devtools'), [
                'Test Features',
                'UI Showcase',
                'UML / Architecture',
                'Demo UI',
            ]),
            'derived_group_entries_allowed' => $this->derivedOperationsEntriesAllowed(),
            'no_nested_users_item' => !$this->groupContainsLabel($usersGroup, 'Users'),
            'no_operations_inside_configuration' => !$this->groupContainsLabel($configurationGroup, 'Operations'),
            'account_recovery_under_users' => $this->groupContainsHref($usersGroup, '/admin/account-recovery'),
            'single_devtools_section' => count(array_filter($groupLabels, static fn (string $label): bool => $label === 'Devtools')) === 1
                && !in_array('Devtools', $titles, true),
            'organization_hierarchy_projected' => $organizationItem !== null,
            'organization_hierarchy_under_users' => $this->groupContainsHref($usersGroup, '/users/organization-hierarchy'),
            'organization_hierarchy_active' => (bool)($organizationItem['is_active'] ?? false),
            'registry_presenter_wired' => $this->shellConsumesRegistryPresenter(),
        ];
        $success = !in_array(false, $checks, true);
        $payload = [
            'success' => $success,
            'checks' => $checks,
            'missing_hrefs' => $missingHrefs,
            'missing_canonical_hrefs' => $missingCanonicalHrefs,
            'titles' => $titles,
            'groups' => $groupLabels,
        ];

        if ($json) {
            $this->line((string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $success ? 0 : 1;
        }

        $this->line('');
        $this->info('Shell Navigation Smoke');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-42s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        if ($missingHrefs !== []) {
            $this->line('  Missing hrefs: ' . implode(', ', $missingHrefs));
        }
        if ($missingCanonicalHrefs !== []) {
            $this->line('  Missing canonical hrefs: ' . implode(', ', $missingCanonicalHrefs));
        }
        $this->line(str_repeat('-', 74));
        $success ? $this->success('Shell navigation projection is coherent.') : $this->error('Shell navigation projection has issues.');
        $this->line('');

        return $success ? 0 : 1;
    }

    /**
     * Extracts expected top-level and child hrefs from shell declarations.
     *
     * @param array<int, array<string, mixed>> $definitions
     * @return string[]
     */
    private function shellHrefs(array $definitions): array
    {
        return $this->collectHrefs($definitions, true);
    }

    /**
     * Identifies manifest parent nodes that should not become separate sidebar links.
     *
     * @param array<string, mixed> $item
     */
    private function isConceptualParent(array $item): bool
    {
        return (string)($item['href'] ?? '') === '/operations'
            && (array)($item['children'] ?? []) !== [];
    }

    /**
     * Extracts hrefs from the rendered sidebar view model.
     *
     * @param array<int, array<string, mixed>> $sidebar
     * @return string[]
     */
    private function sidebarHrefs(array $sidebar): array
    {
        return $this->collectHrefs($sidebar);
    }

    /**
     * Finds one sidebar item by href.
     *
     * @param array<int, array<string, mixed>> $sidebar
     * @return array<string, mixed>|null
     */
    private function findSidebarItem(array $sidebar, string $href): ?array
    {
        foreach ($sidebar as $node) {
            if (!is_array($node)) {
                continue;
            }

            if ((string)($node['href'] ?? '') === $href) {
                return $node;
            }

            $match = $this->findSidebarItem((array)($node['children'] ?? []), $href);
            if ($match !== null) {
                return $match;
            }
        }

        return null;
    }

    /**
     * Finds one sidebar collapse group by label.
     *
     * @param array<int, array<string, mixed>> $sidebar
     * @return array<string, mixed>|null
     */
    private function findSidebarGroup(array $sidebar, string $label): ?array
    {
        foreach ($sidebar as $group) {
            if (is_array($group)
                && ($group['is_container'] ?? false) === true
                && (string)($group['label'] ?? '') === $label
            ) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Extracts visible sidebar section titles.
     *
     * @param array<int, array<string, mixed>> $sidebar
     * @return string[]
     */
    private function sidebarTitles(array $sidebar): array
    {
        $titles = [];
        foreach ($sidebar as $group) {
            if (is_array($group) && ($group['is_title'] ?? false) === true) {
                $titles[] = (string)($group['label'] ?? '');
            }
        }

        return $titles;
    }

    /**
     * Extracts collapse group labels in render order.
     *
     * @param array<int, array<string, mixed>> $sidebar
     * @return string[]
     */
    private function sidebarGroupLabels(array $sidebar): array
    {
        $labels = [];
        foreach ($sidebar as $group) {
            if (is_array($group) && ($group['is_container'] ?? false) === true) {
                $labels[] = (string)($group['label'] ?? '');
            }
        }

        return $labels;
    }

    /**
     * Checks whether canonical group item labels are present in order.
     *
     * @param array<string, mixed>|null $group
     * @param string[] $expectedLabels
     */
    private function groupContainsLabelsInOrder(?array $group, array $expectedLabels): bool
    {
        if ($group === null) {
            return false;
        }

        $labels = [];
        foreach ((array)($group['children'] ?? []) as $item) {
            if (is_array($item)) {
                $labels[] = (string)($item['label'] ?? '');
            }
        }

        $offset = 0;
        foreach ($expectedLabels as $expected) {
            $index = array_search($expected, array_slice($labels, $offset), true);
            if ($index === false) {
                return false;
            }

            $offset += $index + 1;
        }

        return true;
    }

    /**
     * Simulates derived app entries in a canonical group.
     */
    private function derivedOperationsEntriesAllowed(): bool
    {
        return $this->groupContainsLabelsInOrder([
            'children' => [
                ['label' => 'Deployments', 'href' => '/operations/deployments'],
                ['label' => 'Tenancy', 'href' => '/operations/tenancy'],
                ['label' => 'Audit Log', 'href' => '/operations/audit-log'],
                ['label' => 'API Platform', 'href' => '/operations/api-platform'],
                ['label' => 'Automation Rules', 'href' => '/operations/automation-rules'],
                ['label' => 'RTM Profile', 'href' => '/rtm/profile'],
                ['label' => 'RTM Radio', 'href' => '/rtm/radio'],
            ],
        ], [
            'Deployments',
            'Tenancy',
            'Audit Log',
            'API Platform',
            'Automation Rules',
        ]);
    }

    /**
     * Checks whether a group contains a direct item label.
     *
     * @param array<string, mixed>|null $group
     */
    private function groupContainsLabel(?array $group, string $label): bool
    {
        if ($group === null) {
            return false;
        }

        foreach ((array)($group['children'] ?? []) as $item) {
            if (is_array($item) && (string)($item['label'] ?? '') === $label) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a sidebar collapse group contains a direct item href.
     *
     * @param array<string, mixed>|null $group
     */
    private function groupContainsHref(?array $group, string $href): bool
    {
        if ($group === null) {
            return false;
        }

        foreach ((array)($group['children'] ?? []) as $item) {
            if (is_array($item) && (string)($item['href'] ?? '') === $href) {
                return true;
            }
        }

        return false;
    }

    /**
     * Confirms that canonical destinations without an active owner remain visible but non-clickable.
     *
     * @param array<int, array<string, mixed>> $sidebar
     */
    private function disconnectedCanonicalDebtMarked(array $sidebar): bool
    {
        foreach ([
            '/workspaces/module-designer',
            '/workspaces/locale-tools',
            '/operations/deployments',
            '/operations/tenancy',
        ] as $href) {
            $item = $this->findSidebarItem($sidebar, $href);
            if ($item === null
                || empty($item['is_disabled'])
                || (string)($item['badge_label'] ?? '') !== 'Disconnected'
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Collects hrefs from nodes at arbitrary depth.
     *
     * @param array<int, mixed> $nodes
     * @return string[]
     */
    private function collectHrefs(array $nodes, bool $skipConceptualParents = false): array
    {
        $hrefs = [];

        foreach ($nodes as $node) {
            if (!is_array($node)) {
                continue;
            }

            $href = trim((string)($node['href'] ?? ''));
            if ($href !== '' && (!$skipConceptualParents || !$this->isConceptualParent($node))) {
                $hrefs[] = $href;
            }

            $hrefs = array_merge(
                $hrefs,
                $this->collectHrefs((array)($node['children'] ?? $node['items'] ?? []), $skipConceptualParents)
            );
        }

        return array_values(array_unique($hrefs));
    }

    /**
     * Checks the runtime shell bridge source.
     */
    private function shellConsumesRegistryPresenter(): bool
    {
        $scopePath = PD . DS . 'app' . DS . 'Framework' . DS . 'View' . DS . 'DocumentScope.php';
        $providerPath = PD . DS . 'app' . DS . 'Framework' . DS . 'Navigation' . DS
            . 'FrameworkAdminNavigationProvider.php';
        $scope = is_file($scopePath) ? (string)file_get_contents($scopePath) : '';
        $provider = is_file($providerPath) ? (string)file_get_contents($providerPath) : '';

        return str_contains($scope, 'NavigationModelSelector::getInstance()->select')
            && str_contains($provider, 'NavigationRegistry::getInstance()->shell')
            && str_contains($provider, 'ShellNavigationPresenter::fromShell');
    }
}
