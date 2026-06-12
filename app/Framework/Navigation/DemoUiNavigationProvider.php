<?php

declare(strict_types=1);

namespace Catalyst\Framework\Navigation;

/**
 * Provides the Demo UI catalog navigation model.
 *
 * Responsibility: Builds the approved Framework links and complete component catalog from Demo UI catalog data.
 */
final class DemoUiNavigationProvider implements NavigationModelProvider
{
    public const ID = 'demo-ui';

    /**
     * Returns the semantic model identifier.
     */
    public function id(): string
    {
        return self::ID;
    }

    /**
     * Builds Demo UI navigation from provider-owned catalog nodes.
     *
     * @param array<string, mixed> $context
     * @return list<array<string, mixed>>
     */
    public function provide(array $context): array
    {
        $sections = (array) ($context['sections'] ?? []);
        $chartFamilies = (array) ($context['chart_families'] ?? []);
        $chartPages = (array) ($context['chart_pages'] ?? []);
        $tableFamilies = (array) ($context['table_families'] ?? []);
        $tablePages = (array) ($context['table_pages'] ?? []);
        $catalogs = (array) ($context['catalogs'] ?? []);

        if ($sections === []
            && $chartFamilies === []
            && $chartPages === []
            && $tableFamilies === []
            && $tablePages === []
            && $catalogs === []
        ) {
            return [];
        }

        $selectedFile = (string) ($context['selected_file'] ?? '');
        $selectedSection = (string) ($context['selected_section'] ?? '');
        $nodes = [
            ['kind' => 'title', 'label' => __('ui.product_nav.framework')],
            [
                'kind' => 'link',
                'label' => __('ui.product_nav.groups.configuration'),
                'href' => '/configuration/environment-setup',
                'icon' => 'ti ti-settings-cog',
            ],
            [
                'kind' => 'link',
                'label' => __('ui.product_nav.groups.operations'),
                'href' => '/operations/deployments',
                'icon' => 'ti ti-briefcase-2',
            ],
            [
                'kind' => 'link',
                'label' => __('ui.product_nav.groups.users'),
                'href' => '/users',
                'icon' => 'ti ti-users',
            ],
            ['kind' => 'title', 'label' => __('ui.product_nav.components')],
            $this->componentGroup(
                __('ui.product_nav.items.base_ui'),
                'base-ui',
                'ti ti-diamonds',
                $this->flatItems((array) ($sections['base-ui'] ?? []), $catalogs, $selectedFile),
                $selectedSection
            ),
            $this->componentGroup(
                __('ui.product_nav.items.charts'),
                'charts',
                'ti ti-chart-donut',
                $this->familyItems($chartFamilies, $chartPages, $selectedFile),
                $selectedSection
            ),
            $this->componentGroup(
                __('ui.product_nav.items.forms'),
                'forms',
                'ti ti-clipboard-text',
                $this->flatItems((array) ($sections['forms'] ?? []), $catalogs, $selectedFile),
                $selectedSection
            ),
            $this->componentGroup(
                __('ui.product_nav.items.tables'),
                'tables',
                'ti ti-table-options',
                array_merge(
                    $this->tableRootItems($tablePages, $selectedFile),
                    $this->familyItems($tableFamilies, $tablePages, $selectedFile)
                ),
                $selectedSection
            ),
        ];

        return NavigationTreeNormalizer::normalize(
            $nodes,
            (string) ($context['current_path'] ?? '/')
        );
    }

    /**
     * Builds one component catalog container.
     *
     * @param list<array<string, mixed>> $children
     * @return array<string, mixed>
     */
    private function componentGroup(
        string $label,
        string $key,
        string $icon,
        array $children,
        string $selectedSection
    ): array {
        return [
            'kind' => 'container',
            'label' => $label,
            'icon' => $icon,
            'collapse_id' => 'demo-' . $key,
            'is_active' => $selectedSection === $key,
            'children' => $children,
        ];
    }

    /**
     * Builds links for a flat Demo UI section.
     *
     * @param array<int, mixed> $items
     * @param array<int|string, mixed> $catalogs
     * @return list<array<string, mixed>>
     */
    private function flatItems(array $items, array $catalogs, string $selectedFile): array
    {
        $links = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $file = (string) ($item['file'] ?? '');
            $href = $this->routeForFile($file, $catalogs);
            if ($file === '' || $href === '') {
                continue;
            }

            $links[] = [
                'kind' => 'link',
                'label' => (string) ($item['label'] ?? $file),
                'href' => $href,
                'is_active' => $file === $selectedFile,
            ];
        }

        return $links;
    }

    /**
     * Builds recursively grouped chart or table families.
     *
     * @param array<string, mixed> $families
     * @param array<string, mixed> $pages
     * @return list<array<string, mixed>>
     */
    private function familyItems(array $families, array $pages, string $selectedFile): array
    {
        $groups = [];

        foreach ($families as $family => $definition) {
            if (!is_array($definition)) {
                continue;
            }

            $children = [];
            foreach ((array) ($definition['slugs'] ?? []) as $slug) {
                $page = $pages[(string) $slug] ?? null;
                if (!is_array($page)) {
                    continue;
                }

                $file = (string) ($page['file'] ?? '');
                $href = (string) ($page['route'] ?? '');
                if ($file === '' || $href === '') {
                    continue;
                }

                $children[] = [
                    'kind' => 'link',
                    'label' => (string) ($page['label'] ?? $slug),
                    'href' => $href,
                    'is_active' => $file === $selectedFile,
                ];
            }

            if ($children === []) {
                continue;
            }

            $groups[] = [
                'kind' => 'container',
                'label' => (string) ($definition['label'] ?? ucfirst((string) $family)),
                'collapse_id' => 'demo-' . (string) $family,
                'badge_label' => (string) ($definition['badge'] ?? ''),
                'badge_class' => 'badge bg-success text-white',
                'children' => $children,
            ];
        }

        return $groups;
    }

    /**
     * Builds direct Static and Custom table links.
     *
     * @param array<string, mixed> $pages
     * @return list<array<string, mixed>>
     */
    private function tableRootItems(array $pages, string $selectedFile): array
    {
        $items = [];

        foreach (['static', 'custom'] as $slug) {
            $page = $pages[$slug] ?? null;
            if (!is_array($page)) {
                continue;
            }

            $file = (string) ($page['file'] ?? '');
            $href = (string) ($page['route'] ?? '');
            if ($file === '' || $href === '') {
                continue;
            }

            $items[] = [
                'kind' => 'link',
                'label' => (string) ($page['label'] ?? ucfirst($slug)),
                'href' => $href,
                'is_active' => $file === $selectedFile,
            ];
        }

        return $items;
    }

    /**
     * Resolves a catalog file to its canonical Demo UI route.
     *
     * @param array<int|string, mixed> $catalogs
     */
    private function routeForFile(string $file, array $catalogs): string
    {
        foreach ($catalogs as $page) {
            if (is_array($page) && (string) ($page['file'] ?? '') === $file) {
                return (string) ($page['route'] ?? '');
            }
        }

        return '';
    }
}
