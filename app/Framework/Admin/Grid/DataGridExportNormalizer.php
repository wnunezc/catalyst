<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

final class DataGridExportNormalizer
{
    public function __construct(
        private readonly DataGridUrlBuilder $urlBuilder
    ) {
    }

    /**
     * Normalize export formats into render-ready export links.
     *
     * @param array<int|string, string|array<string, mixed>> $exportFormats
     * @param array<string, mixed> $state
     * @param array<string, mixed> $config
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $exportFormats, array $state, array $config): array
    {
        $exports = [];

        foreach ($exportFormats as $format => $definition) {
            if (is_string($definition)) {
                $format = $definition;
                $definition = [];
            }

            $format = (string) $format;

            if ($format === '') {
                continue;
            }

            $query = $this->urlBuilder->mergeQuery((array) ($state['query'] ?? []), [
                (string) ($config['query_param_export'] ?? 'export') => $format,
                (string) ($config['query_param_page'] ?? 'page') => null,
            ]);

            $exports[] = [
                'format' => $format,
                'label' => (string) ($definition['label'] ?? strtoupper($format)),
                'icon' => (string) ($definition['icon'] ?? $this->defaultIcon($format)),
                'href' => $this->urlBuilder->build((string) ($config['base_url'] ?? ''), $query),
                'attributes' => (array) ($definition['attributes'] ?? []),
                'is_print' => false,
            ];
        }

        if (!empty($config['print_enabled'])) {
            $exports[] = [
                'format' => 'print',
                'label' => (string) ($config['print_label'] ?? 'Print'),
                'icon' => (string) ($config['print_icon'] ?? 'fa-solid fa-print'),
                'href' => '#',
                'attributes' => [],
                'is_print' => true,
            ];
        }

        return $exports;
    }

    private function defaultIcon(string $format): string
    {
        return match (strtolower($format)) {
            'csv' => 'fa-solid fa-file-csv',
            'xls', 'xlsx' => 'fa-solid fa-file-excel',
            default => 'fa-solid fa-download',
        };
    }
}