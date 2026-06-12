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

namespace Catalyst\Framework\Navigation;

use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Builds runtime navigation from active module declarations.
 *
 * @package Catalyst\Framework\Navigation
 * Responsibility: Resolves global shell, public, application, breadcrumbs, and visibility rules.
 */
final class NavigationRegistry
{
    use SingletonTrait;

    /**
     * @var array<string, array{label_key: string, description_key: string, icon: string, order: int}>
     */
    private const SHELL_CONTEXTS = [
        'configuration' => [
            'label_key' => 'ui.product_nav.groups.configuration',
            'description_key' => 'ui.product_nav.descriptions.configuration',
            'icon' => 'ti ti-settings-cog',
            'order' => 10,
        ],
        'workspaces' => [
            'label_key' => 'ui.product_nav.groups.workspaces',
            'description_key' => 'ui.product_nav.descriptions.workspaces',
            'icon' => 'ti ti-layout-grid',
            'order' => 20,
        ],
        'operations' => [
            'label_key' => 'ui.product_nav.groups.operations',
            'description_key' => 'ui.product_nav.descriptions.operations',
            'icon' => 'ti ti-briefcase-2',
            'order' => 30,
        ],
        'users' => [
            'label_key' => 'ui.product_nav.groups.users',
            'description_key' => 'ui.product_nav.descriptions.users',
            'icon' => 'ti ti-users',
            'order' => 40,
        ],
        'devtools' => [
            'label_key' => 'ui.product_nav.groups.devtools',
            'description_key' => 'ui.product_nav.descriptions.devtools',
            'icon' => 'ti ti-flask',
            'order' => 90,
        ],
    ];

    /**
     * Builds the global navigation shell for the current path and user.
     *
     * Responsibility: Builds the global navigation shell for the current path and user.
     * @return array<string, mixed>
     */
    public function shell(string $currentPath, ?array $user = null): array
    {
        $itemsByContext = [];

        foreach (ModuleRegistry::getInstance()->active() as $module) {
            foreach ((array)($module['navigation']['shell'] ?? []) as $item) {
                if (!is_array($item)
                    || !$this->hasShellAuthorizationRule((array)($item['visibility'] ?? []))
                    || !$this->isVisible((array)($item['visibility'] ?? []), $user)
                ) {
                    continue;
                }

                $context = (string)($item['context'] ?? 'workspaces');
                $matches = array_values(array_filter((array)($item['matches'] ?? [$item['href'] ?? '/']), 'is_string'));
                $item['matches'] = $matches;
                $item['active'] = $this->matchesAny($currentPath, $matches);
                $itemsByContext[$context][] = $item;
            }
        }

        foreach ($itemsByContext as &$items) {
            usort($items, static function (array $left, array $right): int {
                return [(int)($left['order'] ?? 999), (string)($left['label'] ?? '')]
                    <=> [(int)($right['order'] ?? 999), (string)($right['label'] ?? '')];
            });
        }
        unset($items);

        $activeContext = $this->resolveActiveContext($currentPath, $itemsByContext);
        $contexts = [];

        foreach (self::SHELL_CONTEXTS as $key => $contextMeta) {
            if (empty($itemsByContext[$key])) {
                continue;
            }

            $firstItem = $itemsByContext[$key][0] ?? [];
            $contexts[] = [
                'key' => $key,
                'label' => $this->translateContextMeta($contextMeta['label_key']),
                'description' => $this->translateContextMeta($contextMeta['description_key']),
                'icon' => $contextMeta['icon'],
                'href' => (string)($firstItem['href'] ?? '/'),
                'active' => $key === $activeContext,
            ];
        }

        usort($contexts, static function (array $left, array $right): int {
            return (self::SHELL_CONTEXTS[$left['key']]['order'] ?? 999)
                <=> (self::SHELL_CONTEXTS[$right['key']]['order'] ?? 999);
        });

        $activeItems = $itemsByContext[$activeContext] ?? [];
        $activeMeta = self::SHELL_CONTEXTS[$activeContext] ?? self::SHELL_CONTEXTS['workspaces'];

        return [
            'activeKey' => $activeContext,
            'active' => [
                'label' => $this->translateContextMeta($activeMeta['label_key']),
                'description' => $this->translateContextMeta($activeMeta['description_key']),
                'icon' => $activeMeta['icon'],
                'items' => $activeItems,
            ],
            'groups' => $this->adminGroupsFromItems($itemsByContext, $activeContext),
            'contexts' => $contexts,
        ];
    }

    /**
     * Builds all visible shell context groups for sidebar rendering.
     *
     * Responsibility: Preserves registry-declared item metadata while exposing the full shell tree.
     * @param array<string, array<int, array<string, mixed>>> $itemsByContext
     * @return array<int, array<string, mixed>>
     */
    private function adminGroupsFromItems(array $itemsByContext, string $activeContext): array
    {
        $groups = [];
        $orderedContextKeys = array_values(array_unique(array_merge(
            array_keys(self::SHELL_CONTEXTS),
            array_keys($itemsByContext)
        )));

        foreach ($orderedContextKeys as $key) {
            $items = $itemsByContext[$key] ?? [];
            if ($items === []) {
                continue;
            }

            $meta = self::SHELL_CONTEXTS[$key] ?? [
                'label_key' => (string)($items[0]['group_label'] ?? ucfirst(str_replace('-', ' ', (string)$key))),
                'description_key' => '',
                'icon' => (string)($items[0]['icon'] ?? 'ti ti-layout-sidebar'),
                'order' => (int)($items[0]['group_order'] ?? 999),
            ];

            $groups[] = [
                'key' => (string)$key,
                'label' => $this->translateContextMeta((string)$meta['label_key']),
                'description' => $this->translateContextMeta((string)$meta['description_key']),
                'icon' => (string)$meta['icon'],
                'order' => (int)($meta['order'] ?? 999),
                'href' => (string)($items[0]['href'] ?? '/'),
                'active' => $key === $activeContext,
                'items' => $items,
            ];
        }

        usort($groups, static function (array $left, array $right): int {
            return [(int)($left['order'] ?? 999), (string)($left['label'] ?? '')]
                <=> [(int)($right['order'] ?? 999), (string)($right['label'] ?? '')];
        });

        return $groups;
    }

    /**
     * Resolves the breadcrumb trail matching the current path.
     *
     * Responsibility: Resolves the breadcrumb trail matching the current path.
     * @return array<string, string|null>
     */
    public function breadcrumbs(string $currentPath, ?array $user = null): array
    {
        $rules = [];

        foreach (ModuleRegistry::getInstance()->active() as $module) {
            foreach ((array)($module['navigation']['breadcrumbs'] ?? []) as $rule) {
                if (!is_array($rule) || !$this->isVisible((array)($rule['visibility'] ?? []), $user)) {
                    continue;
                }

                $rules[] = $rule;
            }
        }

        usort($rules, static function (array $left, array $right): int {
            return strlen((string)($right['pattern'] ?? '')) <=> strlen((string)($left['pattern'] ?? ''));
        });

        foreach ($rules as $rule) {
            if (!ModuleRegistry::pathMatches($currentPath, (string)($rule['pattern'] ?? ''))) {
                continue;
            }

            $trail = [];
            foreach ((array)($rule['trail'] ?? []) as $segment) {
                if (!is_array($segment) || ($segment['label'] ?? '') === '') {
                    continue;
                }

                $trail[(string)$segment['label']] = isset($segment['href'])
                    ? ($segment['href'] !== null ? (string)$segment['href'] : null)
                    : null;
            }

            return $trail;
        }

        return [];
    }

    /**
     * Builds visible public navigation items for the current path.
     *
     * Responsibility: Builds visible public navigation items for the current path.
     * @return array<int, array<string, mixed>>
     */
    public function publicMenu(string $currentPath = '/', ?array $user = null): array
    {
        $items = [];

        foreach (ModuleRegistry::getInstance()->active() as $module) {
            foreach ((array)($module['navigation']['public'] ?? []) as $item) {
                if (!is_array($item) || !$this->isVisible((array)($item['visibility'] ?? []), $user)) {
                    continue;
                }

                $matches = array_values(array_filter((array)($item['matches'] ?? [$item['href'] ?? '/']), 'is_string'));
                $item['matches'] = $matches;
                $item['active'] = $this->matchesAny($currentPath, $matches);
                $item['module_key'] = $module['key'];
                $items[] = $item;
            }
        }

        usort($items, static function (array $left, array $right): int {
            return [(int)($left['order'] ?? 999), (string)($left['label'] ?? '')]
                <=> [(int)($right['order'] ?? 999), (string)($right['label'] ?? '')];
        });

        return $items;
    }

    /**
     * Builds recursive application navigation contributed by active App modules.
     *
     * Responsibility: Filters, orders, and isolates declarative App navigation without allowing controllers to replace the tree.
     * @return list<array<string, mixed>>
     */
    public function application(string $currentPath = '/', ?array $user = null): array
    {
        $nodes = [];

        foreach (ModuleRegistry::getInstance()->active() as $module) {
            if (($module['scope'] ?? '') !== 'App') {
                continue;
            }

            foreach ((array) ($module['navigation']['application'] ?? []) as $node) {
                $prepared = $this->prepareApplicationNode(
                    $node,
                    $currentPath,
                    $user,
                    (string) ($module['key'] ?? '')
                );

                if ($prepared !== null) {
                    $nodes[] = $prepared;
                }
            }
        }

        return $this->sortApplicationNodes($nodes);
    }

    /**
     * Returns navigation declarations contributed by one module.
     *
     * Responsibility: Returns navigation declarations contributed by one module.
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function definitionsForModule(string $moduleKey): array
    {
        $module = ModuleRegistry::getInstance()->findByKey($moduleKey);
        $navigation = is_array($module['navigation'] ?? null)
            ? (array) $module['navigation']
            : [];

        return [
            'shell' => array_values((array) ($navigation['shell'] ?? [])),
            'public' => array_values((array) ($navigation['public'] ?? [])),
            'application' => array_values((array) ($navigation['application'] ?? [])),
            'breadcrumbs' => array_values((array) ($navigation['breadcrumbs'] ?? [])),
        ];
    }

    /**
     * Returns navigation declarations contributed by all modules.
     *
     * Responsibility: Returns navigation declarations contributed by all modules.
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function allDefinitions(): array
    {
        $definitions = [
            'shell' => [],
            'public' => [],
            'application' => [],
            'breadcrumbs' => [],
        ];

        foreach (ModuleRegistry::getInstance()->all() as $module) {
            $moduleKey = (string) ($module['key'] ?? '');
            foreach ($this->definitionsForModule($moduleKey) as $bucket => $items) {
                foreach ($items as $item) {
                    if ($bucket !== 'breadcrumbs') {
                        $item['module_key'] = $moduleKey;
                    }

                    $definitions[$bucket][] = $item;
                }
            }
        }

        return $definitions;
    }

    /**
     * Determines whether any visibility rule group allows an item.
     *
     * Responsibility: Determines whether any visibility rule group allows an item.
     * @param array<int, array<string, mixed>> $visibility
     */
    private function isVisible(array $visibility, ?array $user): bool
    {
        if ($visibility === []) {
            return true;
        }

        foreach ($visibility as $group) {
            if (!is_array($group)) {
                continue;
            }

            if ($this->matchesVisibilityGroup($group, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ensures shell entries are authorized by user roles or permissions.
     *
     * @param array<int, array<string, mixed>> $visibility
     */
    private function hasShellAuthorizationRule(array $visibility): bool
    {
        foreach ($visibility as $group) {
            if (!is_array($group)) {
                continue;
            }

            if ((array)($group['roles_any'] ?? []) !== []
                || (array)($group['permissions_any'] ?? []) !== []
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines whether one visibility rule group matches the user and environment.
     *
     * Responsibility: Determines whether one visibility rule group matches the user and environment.
     * @param array<string, mixed> $group
     */
    private function matchesVisibilityGroup(array $group, ?array $user): bool
    {
        $config = ConfigManager::getInstance();
        $permissions = PermissionRegistry::getInstance();

        if (array_key_exists('configured', $group) && (bool)$group['configured'] !== $config->isConfigured()) {
            return false;
        }

        $environments = array_values(array_filter((array)($group['environments'] ?? []), 'is_string'));
        if ($environments !== [] && !in_array($config->getEnvironment(), $environments, true)) {
            return false;
        }

        if (($group['authenticated'] ?? false) === true && $user === null) {
            return false;
        }

        $rolesAny = array_values(array_filter((array)($group['roles_any'] ?? []), 'is_string'));
        if ($rolesAny !== [] && !$permissions->userHasAnyRole($user, $rolesAny)) {
            return false;
        }

        $permissionsAny = array_values(array_filter((array)($group['permissions_any'] ?? []), 'is_string'));
        if ($permissionsAny !== [] && !$permissions->userHasAnyPermission($user, $permissionsAny)) {
            return false;
        }

        $featureFlagsAll = array_values(array_filter((array) ($group['feature_flags_all'] ?? []), 'is_string'));
        foreach ($featureFlagsAll as $featureFlag) {
            if (!FeatureFlagManager::getInstance()->isEnabledForCurrentUser($featureFlag)) {
                return false;
            }
        }

        $featureFlagsAny = array_values(array_filter((array) ($group['feature_flags_any'] ?? []), 'is_string'));
        if ($featureFlagsAny !== []) {
            $hasEnabledFlag = false;
            foreach ($featureFlagsAny as $featureFlag) {
                if (FeatureFlagManager::getInstance()->isEnabledForCurrentUser($featureFlag)) {
                    $hasEnabledFlag = true;
                    break;
                }
            }

            if (!$hasEnabledFlag) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates and filters one recursive App navigation contribution.
     *
     * Responsibility: Discards malformed or invisible nodes while preserving valid siblings and descendants.
     *
     * @param mixed $node
     * @return array<string, mixed>|null
     */
    private function prepareApplicationNode(
        mixed $node,
        string $currentPath,
        ?array $user,
        string $moduleKey
    ): ?array {
        if (!is_array($node)
            || trim((string) ($node['label'] ?? '')) === ''
            || !$this->isVisible((array) ($node['visibility'] ?? []), $user)
        ) {
            return null;
        }

        $kind = strtolower(trim((string) ($node['kind'] ?? '')));
        if (!in_array($kind, ['title', 'link', 'container'], true)) {
            return null;
        }

        if ($kind === 'link' && trim((string) ($node['href'] ?? '')) === '') {
            return null;
        }

        $children = [];
        foreach ((array) ($node['children'] ?? []) as $child) {
            $preparedChild = $this->prepareApplicationNode($child, $currentPath, $user, $moduleKey);
            if ($preparedChild !== null) {
                $children[] = $preparedChild;
            }
        }

        if ($kind === 'container' && $children === []) {
            return null;
        }

        $node['kind'] = $kind;
        $node['module_key'] = $moduleKey;
        $node['children'] = $this->sortApplicationNodes($children);
        $matches = array_values(array_filter(
            (array) ($node['matches'] ?? [$node['href'] ?? '']),
            'is_string'
        ));
        $node['matches'] = $matches;
        $node['active'] = $this->matchesAny($currentPath, $matches);
        unset($node['visibility']);

        return $node;
    }

    /**
     * Orders one application navigation level deterministically.
     *
     * Responsibility: Applies declaration order without imposing a maximum tree depth.
     *
     * @param list<array<string, mixed>> $nodes
     * @return list<array<string, mixed>>
     */
    private function sortApplicationNodes(array $nodes): array
    {
        usort($nodes, static function (array $left, array $right): int {
            return [(int) ($left['order'] ?? 999), (string) ($left['label'] ?? '')]
                <=> [(int) ($right['order'] ?? 999), (string) ($right['label'] ?? '')];
        });

        return $nodes;
    }

    /**
     * Resolves the active shell navigation context.
     *
     * Responsibility: Resolves the active administrative navigation context.
     * @param array<string, array<int, array<string, mixed>>> $itemsByContext
     */
    private function resolveActiveContext(string $currentPath, array $itemsByContext): string
    {
        foreach ($itemsByContext as $context => $items) {
            foreach ($items as $item) {
                if (($item['active'] ?? false) === true) {
                    return (string)$context;
                }
            }
        }

        return array_key_first($itemsByContext) ?? 'workspaces';
    }

    /**
     * Determines whether a path matches any navigation pattern.
     *
     * Responsibility: Determines whether a path matches any navigation pattern.
     * @param string[] $patterns
     */
    private function matchesAny(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (ModuleRegistry::pathMatches($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Translates context metadata when a localized value exists.
     *
     * Responsibility: Translates context metadata when a localized value exists.
     */
    private function translateContextMeta(string $key): string
    {
        $translated = __($key);

        return $translated !== $key ? $translated : $key;
    }
}
