<?php

declare(strict_types=1);

namespace Catalyst\Framework\Navigation;

use Catalyst\Framework\Module\ModuleRegistry;

/**
 * Normalizes declarative navigation into one recursive sidebar tree.
 *
 * Responsibility: Validates node shape, assigns stable collapse identifiers, and propagates active state through ancestors.
 */
final class NavigationTreeNormalizer
{
    /**
     * Normalizes navigation nodes for the shared sidebar renderer.
     *
     * Responsibility: Converts the shared declarative node contract without selecting a navigation model.
     *
     * @param array<int, array<string, mixed>> $nodes
     * @return list<array<string, mixed>>
     */
    public static function normalize(array $nodes, string $currentPath = '/'): array
    {
        $usedIds = [];

        return self::normalizeNodes($nodes, self::normalizePath($currentPath), [], $usedIds);
    }

    /**
     * Normalizes one level and recursively processes all descendants.
     *
     * @param array<int, mixed> $nodes
     * @param string[] $ancestors
     * @param array<string, int> $usedIds
     * @return list<array<string, mixed>>
     */
    private static function normalizeNodes(
        array $nodes,
        string $currentPath,
        array $ancestors,
        array &$usedIds
    ): array {
        $normalized = [];

        foreach ($nodes as $node) {
            if (!is_array($node)) {
                continue;
            }

            $label = trim((string) ($node['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $kind = self::resolveKind($node);
            if ($kind === null) {
                continue;
            }

            if ($kind === 'title') {
                $normalized[] = [
                    'kind' => 'title',
                    'is_title' => true,
                    'is_link' => false,
                    'is_container' => false,
                    'label' => $label,
                    'children' => [],
                    'is_active' => false,
                    'is_expanded' => false,
                ];
                continue;
            }

            $href = trim((string) ($node['href'] ?? ''));
            $childSource = (array) ($node['children'] ?? []);
            $path = [...$ancestors, $label];
            $children = self::normalizeNodes($childSource, $currentPath, $path, $usedIds);

            if ($kind === 'link' && ($href === '' || $href === '#!')) {
                continue;
            }

            if ($kind === 'container' && $children === []) {
                continue;
            }

            $selfActive = !empty($node['is_active'])
                || !empty($node['active'])
                || ($href !== '' && self::matchesPath($currentPath, $href, (array) ($node['matches'] ?? [])));
            $descendantActive = self::hasActiveNode($children);
            $isActive = $selfActive || $descendantActive;
            $isExpanded = $kind === 'container'
                && ($isActive || !empty($node['show']) || ($node['expanded'] ?? '') === 'true');
            $icon = trim((string) ($node['icon'] ?? ''));
            $badgeLabel = trim((string) ($node['badge_label'] ?? ''));
            $isDisabled = !empty($node['is_disabled']) || ($node['runtime_available'] ?? true) === false;

            $normalizedNode = [
                'kind' => $kind,
                'is_title' => false,
                'is_link' => $kind === 'link',
                'is_container' => $kind === 'container',
                'label' => $label,
                'href' => $href,
                'icon' => $icon,
                'has_icon' => $icon !== '',
                'hint' => trim((string) ($node['hint'] ?? '')),
                'badge_label' => $badgeLabel,
                'badge_class' => trim((string) ($node['badge_class'] ?? 'badge bg-secondary')),
                'has_badge' => $badgeLabel !== '',
                'is_disabled' => $isDisabled,
                'runtime_available' => !$isDisabled,
                'children' => $children,
                'is_active' => $isActive,
                'is_expanded' => $isExpanded,
                'expanded' => $isExpanded ? 'true' : 'false',
                'show' => $isExpanded,
                'link_class' => trim('side-nav-link'
                    . ($isActive ? ' active' : '')
                    . ($isDisabled ? ' disabled' : '')),
            ];

            if ($kind === 'container') {
                $normalizedNode['collapse_id'] = self::uniqueCollapseId(
                    trim((string) ($node['collapse_id'] ?? '')),
                    $path,
                    $usedIds
                );
            }

            $normalized[] = $normalizedNode;
        }

        return $normalized;
    }

    /**
     * Resolves a supported node kind from the shared navigation contract.
     *
     * Responsibility: Keeps legacy producer shapes readable only at the normalization boundary during migration.
     *
     * @param array<string, mixed> $node
     */
    private static function resolveKind(array $node): ?string
    {
        $kind = strtolower(trim((string) ($node['kind'] ?? '')));

        if (in_array($kind, ['title', 'link', 'container'], true)) {
            return $kind;
        }

        return null;
    }

    /**
     * Determines whether a route or declared match activates a node.
     *
     * @param string[] $matches
     */
    private static function matchesPath(string $currentPath, string $href, array $matches): bool
    {
        foreach (array_values(array_unique(array_filter([$href, ...$matches], 'is_string'))) as $pattern) {
            if ($pattern === '/') {
                if ($currentPath === '/') {
                    return true;
                }
                continue;
            }

            if ($currentPath === $pattern
                || str_starts_with($currentPath, rtrim($pattern, '/') . '/')
                || ModuleRegistry::pathMatches($currentPath, $pattern)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a normalized descendant is active.
     *
     * @param list<array<string, mixed>> $nodes
     */
    private static function hasActiveNode(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if (!empty($node['is_active'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates a deterministic unique collapse identifier.
     *
     * @param string[] $path
     * @param array<string, int> $usedIds
     */
    private static function uniqueCollapseId(string $preferred, array $path, array &$usedIds): string
    {
        $base = self::slug($preferred !== '' ? $preferred : implode('-', $path));
        $base = $base !== '' ? $base : 'navigation-node';
        $usedIds[$base] = ($usedIds[$base] ?? 0) + 1;

        return 'nav-' . $base . ($usedIds[$base] > 1 ? '-' . $usedIds[$base] : '');
    }

    /**
     * Converts arbitrary labels and identifiers into safe HTML id fragments.
     */
    private static function slug(string $value): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));

        return trim($slug, '-');
    }

    /**
     * Normalizes the request path used for active-state matching.
     */
    private static function normalizePath(string $path): string
    {
        return (string) (parse_url($path !== '' ? $path : '/', PHP_URL_PATH) ?: '/');
    }
}
