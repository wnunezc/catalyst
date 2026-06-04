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

use Catalyst\Framework\Module\ModuleRegistry;

/**
 * Maps admin navigation to the curated Inspinia sidebar model.
 *
 * @package Catalyst\Framework\Navigation
 * Responsibility: Keeps the admin sidebar organization stable while allowing manifests to expose missing surfaces.
 */
final class AdminShellNavigationPresenter
{
    /**
     * @var array<string, array{title:string,label:string,icon:string,items:array<int, array{label:string,href:string,icon:string,aliases?:string[]}>}>
     */
    private const CANONICAL_GROUPS = [
        'configuration' => [
            'title' => 'Framework Configuration',
            'label' => 'Configuration',
            'icon' => 'ti ti-settings-cog',
            'items' => [
                ['label' => 'Environment Setup', 'href' => '/configuration/environment-setup', 'icon' => 'ti ti-adjustments-cog'],
                ['label' => 'Application Health', 'href' => '/configuration/application-health', 'icon' => 'ti ti-heartbeat'],
                ['label' => 'Platform Appearance', 'href' => '/configuration/platform-appearance', 'icon' => 'ti ti-palette'],
                ['label' => 'Feature Flags', 'href' => '/configuration/feature-flags', 'icon' => 'ti ti-flag-3'],
                ['label' => 'Plugins Management', 'href' => '/configuration/plugins', 'icon' => 'ti ti-plug-connected'],
            ],
        ],
        'workspaces' => [
            'title' => 'Framework Operations',
            'label' => 'Workspaces',
            'icon' => 'ti ti-layout-grid',
            'items' => [
                ['label' => 'Catalogs', 'href' => '/workspaces/catalogs', 'icon' => 'ti ti-books'],
                ['label' => 'Module Designer', 'href' => '/workspaces/module-designer', 'icon' => 'ti ti-template'],
                ['label' => 'Media and Documents Fields', 'href' => '/workspaces/media-fields', 'icon' => 'ti ti-list-details'],
                ['label' => 'Media and Documents Library', 'href' => '/workspaces/media-library', 'icon' => 'ti ti-photo'],
                ['label' => 'Document Template', 'href' => '/workspaces/document-templates', 'icon' => 'ti ti-file-description'],
                ['label' => 'Locale Tools', 'href' => '/workspaces/locale-tools', 'icon' => 'ti ti-language'],
            ],
        ],
        'operations' => [
            'title' => 'Framework Operations',
            'label' => 'Operations',
            'icon' => 'ti ti-briefcase-2',
            'items' => [
                ['label' => 'Deployments', 'href' => '/operations/deployments', 'icon' => 'ti ti-rocket'],
                ['label' => 'Tenancy', 'href' => '/operations/tenancy', 'icon' => 'ti ti-building-community'],
                ['label' => 'Audit Log', 'href' => '/audit-log', 'icon' => 'ti ti-history'],
                ['label' => 'API Platform', 'href' => '/api-platform', 'icon' => 'ti ti-api'],
                ['label' => 'Automation Rules', 'href' => '/automation-rules', 'icon' => 'ti ti-bolt'],
            ],
        ],
        'users' => [
            'title' => 'Framework Operations',
            'label' => 'Users',
            'icon' => 'ti ti-users',
            'items' => [
                ['label' => 'User Management', 'href' => '/users', 'icon' => 'ti ti-user-cog', 'aliases' => []],
                ['label' => 'User Role', 'href' => '/users/roles', 'icon' => 'ti ti-shield-check'],
                ['label' => 'User Permissions', 'href' => '/users/permissions', 'icon' => 'ti ti-key'],
                ['label' => 'User Enroll', 'href' => '/users/enroll', 'icon' => 'ti ti-user-plus'],
                ['label' => 'Organization Hierarchy', 'href' => '/users/organization-hierarchy', 'icon' => 'ti ti-building-hierarchy'],
                ['label' => 'Account Recovery', 'href' => '/admin/account-recovery', 'icon' => 'ti ti-lifebuoy'],
            ],
        ],
        'devtools' => [
            'title' => 'Devtools',
            'label' => 'Devtools',
            'icon' => 'ti ti-flask',
            'items' => [
                ['label' => 'Test Features', 'href' => '/test-features', 'icon' => 'ti ti-flask'],
                ['label' => 'UI Showcase', 'href' => '/test-features/ui-showcase', 'icon' => 'ti ti-layout-dashboard'],
                ['label' => 'UML / Architecture', 'href' => '/uml', 'icon' => 'ti ti-route'],
                ['label' => 'Demo UI', 'href' => '/demo-ui', 'icon' => 'ti ti-components'],
            ],
        ],
    ];

    /**
     * Builds sidebar groups from NavigationRegistry::adminShell().
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fromAdminShell(array $shell, ?string $currentPath = null): array
    {
        return self::buildSidebar(self::flattenShellItems($shell), self::currentPath($currentPath));
    }

    /**
     * Builds a permission-agnostic projection from raw admin definitions.
     *
     * @param array<int, array<string, mixed>> $definitions
     * @return array<int, array<string, mixed>>
     */
    public static function fromAdminDefinitions(array $definitions, string $currentPath = '/'): array
    {
        return self::buildSidebar(self::flattenDefinitionItems($definitions), $currentPath);
    }

    /**
     * Returns canonical hrefs that must remain visible in the admin shell.
     *
     * @return string[]
     */
    public static function canonicalHrefs(): array
    {
        $hrefs = [];
        foreach (self::CANONICAL_GROUPS as $group) {
            foreach ($group['items'] as $item) {
                $hrefs[] = $item['href'];
            }
        }

        return $hrefs;
    }

    /**
     * Builds the current sidebar tree.
     *
     * @param array<string, array<string, mixed>> $registryItemsByHref
     * @return array<int, array<string, mixed>>
     */
    private static function buildSidebar(array $registryItemsByHref, string $currentPath): array
    {
        $navGroups = [];
        $currentTitle = '';

        foreach (self::CANONICAL_GROUPS as $key => $group) {
            $items = [];
            foreach ($group['items'] as $definition) {
                $href = (string)$definition['href'];
                $registryItem = $registryItemsByHref[$href] ?? [];
                $items[] = self::makeItem(
                    (string)$definition['label'],
                    $href,
                    (string)($registryItem['icon'] ?? $definition['icon']),
                    $currentPath,
                    (array)($registryItem['matches'] ?? ($definition['aliases'] ?? [])),
                    $href !== '/users'
                );
            }

            foreach (self::unmappedRegistryItems($registryItemsByHref, self::canonicalHrefs()) as $item) {
                if (self::canonicalGroupForItem($item) !== $key) {
                    continue;
                }

                $href = (string)($item['href'] ?? '');
                if ($href === '') {
                    continue;
                }

                $items[] = self::makeItem(
                    (string)($item['label'] ?? $href),
                    $href,
                    (string)($item['icon'] ?? 'ti ti-point'),
                    $currentPath,
                    (array)($item['matches'] ?? []),
                    $href !== '/users'
                );
            }

            if ($items === []) {
                continue;
            }

            if ($group['title'] !== $currentTitle && $group['title'] !== $group['label']) {
                $navGroups[] = [
                    'is_title' => true,
                    'label' => $group['title'],
                ];
                $currentTitle = $group['title'];
            }

            $isActive = in_array(true, array_column($items, 'is_active'), true);
            $navGroups[] = [
                'is_title' => false,
                'is_link' => false,
                'is_collapse' => true,
                'key' => $key,
                'label' => $group['label'],
                'icon' => $group['icon'],
                'collapse_id' => 'admin-' . $key,
                'is_active' => $isActive,
                'expanded' => $isActive ? 'true' : 'false',
                'show' => $isActive,
                'items' => $items,
            ];
        }

        return $navGroups;
    }

    /**
     * Builds one sidebar item with the preserved template shape.
     *
     * @param string[] $matches
     * @return array<string, mixed>
     */
    private static function makeItem(
        string $label,
        string $href,
        string $icon,
        string $currentPath,
        array $matches = [],
        bool $prefixMatch = true
    ): array {
        $patterns = array_values(array_unique(array_filter(array_merge([$href], $matches), 'is_string')));
        $active = false;

        foreach ($patterns as $pattern) {
            if ($pattern === '/') {
                $active = $active || $currentPath === '/';
                continue;
            }

            $active = $active
                || $currentPath === $pattern
                || ($prefixMatch && str_starts_with($currentPath, $pattern . '/'))
                || ModuleRegistry::pathMatches($currentPath, $pattern);
        }

        return [
            'label' => $label,
            'href' => $href,
            'icon' => $icon,
            'is_active' => $active,
            'link_class' => $active ? 'side-nav-link active' : 'side-nav-link',
            'is_nested_collapse' => false,
            'badge_label' => '',
            'badge_class' => '',
            'children' => [],
        ];
    }

    /**
     * Flattens NavigationRegistry admin shell groups into href-indexed items.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function flattenShellItems(array $shell): array
    {
        $items = [];
        foreach ((array)($shell['groups'] ?? []) as $group) {
            foreach ((array)($group['items'] ?? []) as $item) {
                self::collectItem($items, (array)$item);
            }
        }

        return $items;
    }

    /**
     * Flattens raw admin definitions into href-indexed items.
     *
     * @param array<int, array<string, mixed>> $definitions
     * @return array<string, array<string, mixed>>
     */
    private static function flattenDefinitionItems(array $definitions): array
    {
        $items = [];
        foreach ($definitions as $item) {
            if (is_array($item)) {
                self::collectItem($items, $item);
            }
        }

        return $items;
    }

    /**
     * Adds one item and its children to a href index.
     *
     * @param array<string, array<string, mixed>> $items
     * @param array<string, mixed> $item
     */
    private static function collectItem(array &$items, array $item): void
    {
        $href = trim((string)($item['href'] ?? ''));
        if ($href !== '' && !self::isConceptualParent($item)) {
            $items[$href] = $item;
        }

        foreach ((array)($item['children'] ?? []) as $child) {
            if (!is_array($child)) {
                continue;
            }

            $childHref = trim((string)($child['href'] ?? ''));
            if ($childHref === '') {
                continue;
            }

            $child['context'] = $item['context'] ?? null;
            $items[$childHref] = $child;
        }
    }

    /**
     * Keeps conceptual grouping pages from becoming duplicate sidebar entries.
     *
     * @param array<string, mixed> $item
     */
    private static function isConceptualParent(array $item): bool
    {
        return (string)($item['href'] ?? '') === '/operations'
            && (array)($item['children'] ?? []) !== [];
    }

    /**
     * Returns registry items that are not already represented in the canonical menu.
     *
     * @param array<string, array<string, mixed>> $registryItemsByHref
     * @param string[] $canonicalHrefs
     * @return array<int, array<string, mixed>>
     */
    private static function unmappedRegistryItems(array $registryItemsByHref, array $canonicalHrefs): array
    {
        $items = [];
        foreach ($registryItemsByHref as $href => $item) {
            if (!in_array($href, $canonicalHrefs, true)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Maps non-canonical manifest items into the stable admin groups.
     *
     * @param array<string, mixed> $item
     */
    private static function canonicalGroupForItem(array $item): string
    {
        $href = (string)($item['href'] ?? '');
        $context = (string)($item['context'] ?? '');

        if ($context === 'account-recovery' || str_starts_with($href, '/admin/account-recovery')) {
            return 'users';
        }

        if ($context === 'devtools'
            || str_starts_with($href, '/test-features')
            || str_starts_with($href, '/uml')
            || str_starts_with($href, '/devtools')
        ) {
            return 'devtools';
        }

        return match ($context) {
            'configuration' => 'configuration',
            'workspaces' => 'workspaces',
            'operations' => 'operations',
            'users' => 'users',
            default => str_starts_with($href, '/users') ? 'users' : 'operations',
        };
    }

    /**
     * Resolves the request path used for active state.
     */
    private static function currentPath(?string $currentPath): string
    {
        if ($currentPath !== null && trim($currentPath) !== '') {
            return $currentPath;
        }

        return (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
    }
}
