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
 * Maps registry navigation payloads into the Inspinia sidebar view model.
 *
 * @package Catalyst\Framework\Navigation
 * Responsibility: Keeps admin sidebar rendering registry-driven without coupling templates to module manifests.
 */
final class AdminShellNavigationPresenter
{
    /**
     * Builds sidebar groups from NavigationRegistry::adminShell().
     *
     * Responsibility: Adapts visible runtime navigation to the template contract.
     * @return array<int, array<string, mixed>>
     */
    public static function fromAdminShell(array $shell): array
    {
        return self::groupsToSidebar((array)($shell['groups'] ?? []));
    }

    /**
     * Builds a permission-agnostic sidebar projection from raw admin definitions.
     *
     * Responsibility: Supports smoke/lint checks without requiring a database-backed user session.
     * @param array<int, array<string, mixed>> $definitions
     * @return array<int, array<string, mixed>>
     */
    public static function fromAdminDefinitions(array $definitions, string $currentPath = '/'): array
    {
        $itemsByContext = [];
        foreach ($definitions as $item) {
            if (!is_array($item)) {
                continue;
            }

            $context = (string)($item['context'] ?? 'workspaces');
            $matches = array_values(array_filter((array)($item['matches'] ?? [$item['href'] ?? '/']), 'is_string'));
            $item['matches'] = $matches;
            $item['active'] = self::matchesAny($currentPath, $matches);
            $itemsByContext[$context][] = $item;
        }

        foreach ($itemsByContext as &$items) {
            usort($items, static function (array $left, array $right): int {
                return [(int)($left['order'] ?? 999), (string)($left['label'] ?? '')]
                    <=> [(int)($right['order'] ?? 999), (string)($right['label'] ?? '')];
            });
        }
        unset($items);

        $groups = [];
        foreach ($itemsByContext as $context => $items) {
            $groups[] = [
                'key' => (string)$context,
                'label' => self::contextLabel((string)$context, (string)($items[0]['group_label'] ?? '')),
                'icon' => (string)($items[0]['icon'] ?? 'ti ti-layout-sidebar'),
                'order' => (int)($items[0]['group_order'] ?? 999),
                'active' => in_array(true, array_column($items, 'active'), true),
                'items' => $items,
            ];
        }

        usort($groups, static function (array $left, array $right): int {
            return [(int)($left['order'] ?? 999), (string)($left['label'] ?? '')]
                <=> [(int)($right['order'] ?? 999), (string)($right['label'] ?? '')];
        });

        return self::groupsToSidebar($groups);
    }

    /**
     * Converts grouped navigation payloads to the legacy demo_ui_nav_groups shape.
     *
     * Responsibility: Preserves the current template API while removing hardcoded admin route lists.
     * @param array<int, array<string, mixed>> $groups
     * @return array<int, array<string, mixed>>
     */
    private static function groupsToSidebar(array $groups): array
    {
        $navGroups = [];
        $currentTitle = '';

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }

            $items = self::itemsToSidebar((array)($group['items'] ?? []));
            if ($items === []) {
                continue;
            }

            $title = self::sectionTitle((string)($group['key'] ?? ''));
            if ($title !== '' && $title !== $currentTitle) {
                $navGroups[] = [
                    'is_title' => true,
                    'label' => $title,
                ];
                $currentTitle = $title;
            }

            $isActive = in_array(true, array_column($items, 'is_active'), true);
            $key = (string)($group['key'] ?? ('admin-' . count($navGroups)));

            $navGroups[] = [
                'is_title' => false,
                'is_link' => false,
                'is_collapse' => true,
                'key' => $key,
                'label' => (string)($group['label'] ?? ucfirst(str_replace('-', ' ', $key))),
                'icon' => (string)($group['icon'] ?? 'ti ti-layout-sidebar'),
                'collapse_id' => 'admin-' . preg_replace('/[^a-z0-9_-]+/i', '-', $key),
                'is_active' => $isActive,
                'expanded' => $isActive ? 'true' : 'false',
                'show' => $isActive,
                'items' => $items,
            ];
        }

        return $navGroups;
    }

    /**
     * Maps declared navigation items and children into sidebar links.
     *
     * Responsibility: Keeps item active state, href, icon, hint and nested children from module metadata.
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private static function itemsToSidebar(array $items): array
    {
        $navItems = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $children = self::childrenToSidebar((array)($item['children'] ?? []));
            $isActive = (bool)($item['active'] ?? false)
                || in_array(true, array_column($children, 'is_active'), true);
            $hasChildren = $children !== [];

            $navItems[] = [
                'label' => (string)($item['label'] ?? ''),
                'href' => (string)($item['href'] ?? '#'),
                'icon' => (string)($item['icon'] ?? 'ti ti-point'),
                'hint' => (string)($item['hint'] ?? ''),
                'is_active' => $isActive,
                'link_class' => $isActive ? 'side-nav-link active' : 'side-nav-link',
                'is_nested_collapse' => $hasChildren,
                'collapse_id' => $hasChildren ? self::collapseId((string)($item['href'] ?? ''), count($navItems)) : '',
                'expanded' => $isActive ? 'true' : 'false',
                'show' => $isActive,
                'badge_label' => (string)($item['badge_label'] ?? ''),
                'badge_class' => (string)($item['badge_class'] ?? ''),
                'children' => $children,
            ];
        }

        return $navItems;
    }

    /**
     * Maps child navigation declarations to nested sidebar links.
     *
     * Responsibility: Preserves child route visibility in the sidebar model.
     * @param array<int, array<string, mixed>> $children
     * @return array<int, array<string, mixed>>
     */
    private static function childrenToSidebar(array $children): array
    {
        $navChildren = [];
        foreach ($children as $child) {
            if (!is_array($child)) {
                continue;
            }

            $matches = array_values(array_filter((array)($child['matches'] ?? [$child['href'] ?? '/']), 'is_string'));
            $isActive = (bool)($child['active'] ?? false)
                || self::matchesAny((string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/'), $matches);

            $navChildren[] = [
                'label' => (string)($child['label'] ?? ''),
                'href' => (string)($child['href'] ?? '#'),
                'hint' => (string)($child['hint'] ?? ''),
                'is_active' => $isActive,
                'link_class' => $isActive ? 'side-nav-link active' : 'side-nav-link',
            ];
        }

        return $navChildren;
    }

    /**
     * Assigns broad sidebar section titles compatible with the current shell.
     */
    private static function sectionTitle(string $context): string
    {
        return match ($context) {
            'configuration' => 'Framework Configuration',
            'workspaces', 'operations', 'users' => 'Framework Operations',
            default => 'Devtools',
        };
    }

    /**
     * Returns the sidebar label for a registry context.
     */
    private static function contextLabel(string $context, string $fallback): string
    {
        return match ($context) {
            'configuration' => 'Configuration',
            'workspaces' => 'Workspaces',
            'operations' => 'Operations',
            'users' => 'Users',
            'devtools' => 'Devtools',
            default => $fallback !== '' ? $fallback : ucfirst(str_replace('-', ' ', $context)),
        };
    }

    /**
     * Creates stable collapse ids for nested item groups.
     */
    private static function collapseId(string $href, int $index): string
    {
        $slug = trim((string)preg_replace('/[^a-z0-9_-]+/i', '-', trim($href, '/')), '-');

        return 'admin-item-' . ($slug !== '' ? $slug : (string)$index);
    }

    /**
     * Determines whether a path matches any declared navigation pattern.
     *
     * @param string[] $patterns
     */
    private static function matchesAny(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (ModuleRegistry::pathMatches($path, (string)$pattern)) {
                return true;
            }
        }

        return false;
    }
}
