<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

final class DataGridBulkActionNormalizer
{
    public function __construct(
        private readonly DataGridUrlBuilder $urlBuilder
    ) {
    }

    /**
     * Normalize configured bulk actions into render-ready definitions.
     *
     * @param array<int, array<string, mixed>> $bulkActions
     * @param array<string, mixed> $state
     * @param array<string, mixed> $config
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $bulkActions, array $state, array $config): array
    {
        $normalized = [];

        foreach ($bulkActions as $action) {
            $name = (string) ($action['name'] ?? '');
            $href = (string) ($action['href'] ?? '#');

            if ($name === '') {
                $name = $href !== '#'
                    ? trim((string) preg_replace('~[^a-z0-9]+~i', '-', $href), '-')
                    : 'bulk-action-' . (count($normalized) + 1);
            }

            $normalized[] = [
                'name' => $name,
                'label' => (string) ($action['label'] ?? $name),
                'method' => strtoupper((string) ($action['method'] ?? 'POST')),
                'href' => $this->urlBuilder->build(
                    $href,
                    $this->urlBuilder->mergeQuery((array) ($state['query'] ?? []), [
                        (string) ($config['query_param_export'] ?? 'export') => null,
                    ])
                ),
                'confirm' => (string) ($action['confirm'] ?? ''),
                'icon' => (string) ($action['icon'] ?? ''),
                'class' => (string) ($action['class'] ?? 'btn btn-outline-secondary btn-sm'),
                'variant' => (string) ($action['variant'] ?? 'secondary'),
                'attributes' => (array) ($action['attributes'] ?? []),
            ];
        }

        return $normalized;
    }
}
