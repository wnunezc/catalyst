<?php


declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

final class DataGridFilterNormalizer
{
    public function __construct(
        private readonly DataGridTextFormatter $textFormatter
    )
    {
    }

    /**
     * Normalize configured filters for rendering.
     *
     * @param array<int, array<string, mixed>> $filters
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $filters, array $state): array
    {
        $normalized = [];

        foreach ($filters as $filter) {
            $name = (string)($filter['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $options = [];
            foreach ((array) ($filter['options'] ?? []) as $value => $option) {
                if (is_array($option)) {
                    $optionValue = (string) ($option['value'] ?? (is_string($value) ? $value : ''));
                    $optionLabel = (string) ($option['label'] ?? $optionValue);
                } else {
                    $optionValue = is_string($value) ? $value : (string) $option;
                    $optionLabel = (string) $option;
                }

                $options[] = [
                    'value' => $optionValue,
                    'label' => $optionLabel,
                ];
            }

            $normalized[] = [
                'name' => $name,
                'label' => (string)($filter['label'] ?? $this->textFormatter->humanize($name)),
                'type' => (string)($filter['type'] ?? 'text'),
                'value' => $state['filters'][$name] ?? ($filter['default'] ?? ''),
                'placeholder' => (string)($filter['placeholder'] ?? ''),
                'options' => $options,
                'attributes' => (array)($filter['attributes'] ?? []),
            ];
        }

        return $normalized;
    }
}
