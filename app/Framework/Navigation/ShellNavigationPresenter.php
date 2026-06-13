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
 * Maps shell navigation to the curated Inspinia sidebar model.
 *
 * @package Catalyst\Framework\Navigation
 * Responsibility: Keeps the global sidebar organization stable while allowing manifests to expose missing surfaces.
 */
final class ShellNavigationPresenter
{
    /**
     * @var array<string, array{title:string,label:string,icon:string,items:array<int, array{label:string,href:string,icon:string,aliases?:string[]}>}>
     */
    private const CANONICAL_GROUPS = [
        'configuration' => [
            'title' => 'ui.product_nav.titles.configuration',
            'label' => 'ui.product_nav.groups.configuration',
            'icon' => 'ti ti-settings-cog',
            'items' => [
                ['label_key' => 'ui.product_nav.items.environment_setup', 'href' => '/configuration/environment-setup', 'icon' => 'ti ti-adjustments-cog'],
                ['label_key' => 'ui.product_nav.items.application_health', 'href' => '/configuration/application-health', 'icon' => 'ti ti-heartbeat'],
                ['label_key' => 'ui.product_nav.items.platform_appearance', 'href' => '/configuration/platform-appearance', 'icon' => 'ti ti-palette'],
                ['label_key' => 'ui.product_nav.items.feature_flags', 'href' => '/configuration/feature-flags', 'icon' => 'ti ti-flag-3'],
                ['label_key' => 'ui.product_nav.items.plugins_management', 'href' => '/configuration/plugins', 'icon' => 'ti ti-plug-connected'],
            ],
        ],
        'workspaces' => [
            'title' => 'ui.product_nav.titles.operations',
            'label' => 'ui.product_nav.groups.workspaces',
            'icon' => 'ti ti-layout-grid',
            'items' => [
                ['label_key' => 'ui.product_nav.items.catalogs', 'href' => '/workspaces/catalogs', 'icon' => 'ti ti-books'],
                ['label_key' => 'ui.product_nav.items.module_designer', 'href' => '/workspaces/module-designer', 'icon' => 'ti ti-template'],
                ['label_key' => 'ui.product_nav.items.media_fields', 'href' => '/workspaces/media-fields', 'icon' => 'ti ti-list-details'],
                ['label_key' => 'ui.product_nav.items.media_library', 'href' => '/workspaces/media-library', 'icon' => 'ti ti-photo'],
                ['label_key' => 'ui.product_nav.items.document_template', 'href' => '/workspaces/document-templates', 'icon' => 'ti ti-file-description'],
                ['label_key' => 'ui.product_nav.items.locale_tools', 'href' => '/workspaces/locale-tools', 'icon' => 'ti ti-language'],
            ],
        ],
        'operations' => [
            'title' => 'ui.product_nav.titles.operations',
            'label' => 'ui.product_nav.groups.operations',
            'icon' => 'ti ti-briefcase-2',
            'items' => [
                ['label_key' => 'ui.product_nav.items.deployments', 'href' => '/operations/deployments', 'icon' => 'ti ti-rocket'],
                ['label_key' => 'ui.product_nav.items.tenancy', 'href' => '/operations/tenancy', 'icon' => 'ti ti-building-community'],
                ['label_key' => 'ui.product_nav.items.audit_log', 'href' => '/operations/audit-log', 'icon' => 'ti ti-history'],
                ['label_key' => 'ui.product_nav.items.api_management', 'href' => '/operations/api-management', 'icon' => 'ti ti-api'],
                ['label_key' => 'ui.product_nav.items.automation_rules', 'href' => '/operations/automation-rules', 'icon' => 'ti ti-bolt'],
            ],
        ],
        'users' => [
            'title' => 'ui.product_nav.titles.operations',
            'label' => 'ui.product_nav.groups.users',
            'icon' => 'ti ti-users',
            'items' => [
                ['label_key' => 'ui.product_nav.items.user_management', 'href' => '/users', 'icon' => 'ti ti-user-cog', 'aliases' => []],
                ['label_key' => 'ui.product_nav.items.user_role', 'href' => '/users/roles', 'icon' => 'ti ti-shield-check'],
                ['label_key' => 'ui.product_nav.items.user_permissions', 'href' => '/users/permissions', 'icon' => 'ti ti-key'],
                ['label_key' => 'ui.product_nav.items.user_enroll', 'href' => '/users/enroll', 'icon' => 'ti ti-user-plus'],
                ['label_key' => 'ui.product_nav.items.organization_hierarchy', 'href' => '/users/organization-hierarchy', 'icon' => 'ti ti-building-hierarchy'],
                ['label_key' => 'ui.product_nav.items.account_recovery', 'href' => '/users/account-recovery', 'icon' => 'ti ti-lifebuoy'],
            ],
        ],
        'devtools' => [
            'title' => 'ui.product_nav.groups.devtools',
            'label' => 'ui.product_nav.groups.devtools',
            'icon' => 'ti ti-flask',
            'items' => [
                ['label_key' => 'ui.product_nav.items.test_features', 'href' => '/test-features', 'icon' => 'ti ti-flask'],
                ['label_key' => 'ui.product_nav.items.uml_architecture', 'href' => '/uml', 'icon' => 'ti ti-route'],
                ['label_key' => 'ui.product_nav.groups.demo_ui', 'href' => '/demo-ui', 'icon' => 'ti ti-components'],
            ],
        ],
    ];

    /**
     * Builds sidebar groups from NavigationRegistry::shell().
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fromShell(array $shell, ?string $currentPath = null): array
    {
        $path = self::currentPath($currentPath);

        return NavigationTreeNormalizer::normalize(
            self::buildSidebar(self::flattenShellItems($shell), $path),
            $path
        );
    }

    /**
     * Builds a permission-agnostic projection from raw shell definitions.
     *
     * @param array<int, array<string, mixed>> $definitions
     * @return array<int, array<string, mixed>>
     */
    public static function fromDefinitions(array $definitions, string $currentPath = '/'): array
    {
        return NavigationTreeNormalizer::normalize(
            self::buildSidebar(self::flattenDefinitionItems($definitions), $currentPath),
            $currentPath
        );
    }

    /**
     * Returns canonical hrefs that must remain visible in the shell.
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
                $registryItem = $registryItemsByHref[$href] ?? null;
                if (!is_array($registryItem)) {
                    $items[] = self::makeItem(
                        __((string)$definition['label_key']),
                        $href,
                        (string)$definition['icon'],
                        $currentPath,
                        (array)($definition['aliases'] ?? []),
                        $href !== '/users',
                        false
                    );
                    continue;
                }

                $items[] = self::makeItem(
                    __((string)$definition['label_key']),
                    $href,
                    (string)($registryItem['icon'] ?? $definition['icon']),
                    $currentPath,
                    (array)($registryItem['matches'] ?? ($definition['aliases'] ?? [])),
                    $href !== '/users',
                    true
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
                    $href !== '/users',
                    true
                );
            }

            if ($items === []) {
                continue;
            }

            $title = __((string) $group['title']);
            $label = __((string) $group['label']);
            if ($title !== $currentTitle && $title !== $label) {
                $navGroups[] = [
                    'kind' => 'title',
                    'label' => $title,
                ];
                $currentTitle = $title;
            }

            $isActive = in_array(true, array_column($items, 'is_active'), true);
            $navGroups[] = [
                'kind' => 'container',
                'key' => $key,
                'label' => $label,
                'icon' => $group['icon'],
                'collapse_id' => 'shell-' . $key,
                'is_active' => $isActive,
                'children' => $items,
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
        bool $prefixMatch = true,
        bool $runtimeAvailable = true
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
            'kind' => 'link',
            'label' => $label,
            'href' => $href,
            'icon' => $icon,
            'is_active' => $active,
            'badge_class' => 'badge bg-secondary',
            'runtime_available' => $runtimeAvailable,
            'is_disabled' => !$runtimeAvailable,
            'badge_label' => $runtimeAvailable ? '' : __('ui.product_nav.disconnected'),
            'children' => [],
        ];
    }

    /**
     * Flattens NavigationRegistry shell groups into href-indexed items.
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
     * Flattens raw navigation definitions into href-indexed items.
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
            if ($childHref !== '') {
                $child['context'] = $item['context'] ?? null;
                $items[$childHref] = $child;
            }

            self::collectItem($items, array_replace($child, [
                'context' => $child['context'] ?? $item['context'] ?? null,
            ]));
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
     * Maps non-canonical manifest items into the stable shell groups.
     *
     * @param array<string, mixed> $item
     */
    private static function canonicalGroupForItem(array $item): string
    {
        $href = (string)($item['href'] ?? '');
        $context = (string)($item['context'] ?? '');

        if ($context === 'account-recovery' || str_starts_with($href, '/users/account-recovery')) {
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
